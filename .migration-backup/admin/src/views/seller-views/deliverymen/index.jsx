import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Alert,
  Button,
  Card,
  Empty,
  Input,
  Modal,
  Skeleton,
  Space,
  Tag,
  Typography,
  notification,
} from 'antd';
import {
  EditOutlined,
  CopyOutlined,
  PlusOutlined,
  ReloadOutlined,
  SearchOutlined,
  LeftOutlined,
  RightOutlined,
} from '@ant-design/icons';
import { useSelector, shallowEqual } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useNavigationScope } from 'context/navigation-scope';
import deliveryDriverInvitations from 'services/delivery-driver-invitations';
import { getDeliveryDriverPermissions } from './delivery-driver-access.mjs';
import {
  invitationResponsePayload,
  normalizeDriverInvitationLink,
} from './delivery-driver-link.mjs';
import cls from './delivery-driver.module.scss';

const { Title } = Typography;

function responseRows(response) {
  return Array.isArray(response?.data) ? response.data : [];
}

function stateClass(state) {
  if (state === 'active') return `${cls.statePill} ${cls.stateActive}`;
  if (state === 'pending') return `${cls.statePill} ${cls.statePending}`;
  return cls.statePill;
}

function SellerDeliverymen() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const { myShop } = useSelector((state) => state.myShop, shallowEqual);
  const navigationScope = useNavigationScope();
  const { canView, canInvite, shopId: scopedShopId } =
    getDeliveryDriverPermissions(user, navigationScope);
  const shopId = scopedShopId || myShop?.id || null;
  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');
  const [query, setQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [workingId, setWorkingId] = useState(null);
  const [freshLinks, setFreshLinks] = useState({});

  const loadRoster = useCallback(async (quiet = false) => {
    if (!quiet) setLoading(true);
    else setRefreshing(true);
    setError('');
    try {
      const response = await deliveryDriverInvitations.list({
        page: currentPage,
      });
      const responseLastPage = Math.max(
        1,
        Number(response?.meta?.last_page || 1),
      );
      if (currentPage > responseLastPage) {
        setCurrentPage(responseLastPage);
        return;
      }
      setRows(responseRows(response));
      setMeta(response?.meta || null);
    } catch (requestError) {
      setError(
        requestError?.response?.data?.message ||
          'The driver roster could not be loaded. Try again.',
      );
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [currentPage]);

  useEffect(() => {
    if (navigationScope?.status === 'ready' && canView) {
      loadRoster();
    } else if (navigationScope?.status === 'ready') {
      setLoading(false);
    }
  }, [navigationScope?.status, canView, shopId, currentPage, loadRoster]);

  const lastPage = Math.max(1, Number(meta?.last_page || 1));

  const visibleRows = useMemo(() => {
    const normalized = query.trim().toLowerCase();
    if (!normalized) return rows;
    return rows.filter((row) =>
      [
        row?.email,
        row?.firstname,
        row?.lastname,
        row?.user?.firstname,
        row?.user?.lastname,
        row?.state,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()
        .includes(normalized),
    );
  }, [rows, query]);

  const runAction = async (row, action, actionLabel, onSuccess) => {
    setWorkingId(row.id);
    setError('');
    if (actionLabel !== 'Resend') {
      setFreshLinks((current) => {
        const next = { ...current };
        delete next[row.id];
        return next;
      });
    }
    try {
      const response = await action();
      await loadRoster(true);
      if (onSuccess) onSuccess(response);
      notification.success({
        message:
          actionLabel === 'Resend'
            ? 'New invitation link ready'
            : `${actionLabel} completed.`,
        ...(actionLabel === 'Resend'
          ? {
              description:
                'Copy the returned link below to share it securely. This does not confirm notification delivery.',
            }
          : {}),
      });
    } catch (requestError) {
      const status = requestError?.response?.status;
      const serverMessage = requestError?.response?.data?.message;
      const message =
        status === 409
          ? serverMessage ||
            'This driver has active or in-progress deliveries. Finish or reassign them before changing the relationship.'
          : serverMessage || `Could not ${actionLabel.toLowerCase()}. Try again.`;
      setError(message);
    } finally {
      setWorkingId(null);
    }
  };

  const resendInvitation = (row) => {
    setFreshLinks((current) => {
      const next = { ...current };
      delete next[row.id];
      return next;
    });
    runAction(
      row,
      () => deliveryDriverInvitations.resend(row.id),
      'Resend',
      (response) => {
        const payload = invitationResponsePayload(response);
        const link = normalizeDriverInvitationLink(
          payload?.invitation_link || payload?.data?.invitation_link || '',
        );
        if (link) {
          setFreshLinks((current) => ({ ...current, [row.id]: link }));
        } else {
          setError(
            'The invitation was resent, but the response did not include a valid native invitation link to copy.',
          );
        }
      },
    );
  };

  const copyFreshLink = async (rowId) => {
    const link = freshLinks[rowId];
    if (!link) return;
    try {
      await navigator.clipboard.writeText(link);
      notification.success({ message: 'New invitation link copied.' });
    } catch {
      notification.warning({
        message: 'Copy was not available',
        description: 'Select and copy the new invitation link shown in the roster.',
      });
    }
  };

  const confirmAction = (row, action, label, description) => {
    Modal.confirm({
      title: label,
      content: description,
      okText: label,
      cancelText: t('cancel'),
      onOk: () => runAction(row, action, label),
    });
  };

  const renderActions = (row) => {
    const busy = workingId === row.id;
    if (!canInvite) return null;

    if (row.state === 'pending') {
      return (
        <div className={cls.actions}>
          <Button
            size='small'
            loading={busy}
            disabled={Boolean(workingId)}
            onClick={() => resendInvitation(row)}
          >
            Resend
          </Button>
          <Button
            size='small'
            danger
            loading={busy}
            disabled={Boolean(workingId)}
            onClick={() =>
              confirmAction(
                row,
                () => deliveryDriverInvitations.revoke(row.id),
                'Revoke invitation',
                `The pending invitation for ${row.email} will no longer be usable.`,
              )
            }
          >
            Revoke
          </Button>
        </div>
      );
    }

    if (row.state === 'active' || row.state === 'suspended') {
      const activate = row.state === 'suspended';
      return (
        <div className={cls.actions}>
          <Button
            size='small'
            type={activate ? 'primary' : 'default'}
            danger={!activate}
            loading={busy}
            disabled={Boolean(workingId)}
            onClick={() =>
              confirmAction(
                row,
                () => deliveryDriverInvitations.setActive(row.id, activate),
                activate ? 'Reactivate driver' : 'Suspend driver',
                activate
                  ? `Restore this driver's access to ${myShop?.translation?.title || 'this shop'}?`
                  : `Suspend this driver's access to this shop? Existing active or in-progress deliveries must be completed or reassigned first.`,
              )
            }
          >
            {activate ? 'Reactivate' : 'Suspend'}
          </Button>
          {row?.user?.uuid && (
            <Button
              size='small'
              icon={<EditOutlined />}
              aria-label={`Edit operational settings for ${row.email}`}
              onClick={() =>
                navigate(
                  `/seller/invitations/deliverymen/edit/${row.user.uuid}`,
                )
              }
            >
              Settings
            </Button>
          )}
        </div>
      );
    }
    return null;
  };

  return (
    <div className={cls.page}>
      <Card>
        <div className={cls.hero}>
          <div>
            <div className={cls.eyebrow}>Shop team</div>
            <Title level={1} className={cls.title}>
              Delivery drivers
            </Title>
            <p className={cls.subtitle}>
              Invite drivers by email and manage only their relationship with this
              shop. Driver accounts and credentials remain theirs.
            </p>
          </div>
          <Space wrap>
            <Button
              icon={<ReloadOutlined />}
              loading={refreshing}
              disabled={!canView}
              onClick={() => loadRoster(true)}
            >
              Refresh
            </Button>
            {canInvite && (
              <Button
                type='primary'
                icon={<PlusOutlined />}
                onClick={() =>
                  navigate('/seller/invitations/deliverymen/add')
                }
              >
                Invite a driver
              </Button>
            )}
          </Space>
        </div>

        {navigationScope?.status !== 'ready' ? (
          <Skeleton active paragraph={{ rows: 4 }} />
        ) : !canView ? (
          <Alert
            showIcon
            type='warning'
            message='Roster access is not available'
            description='This shop session does not include the staff.view permission. Ask a shop owner or manager to review your access.'
          />
        ) : (
          <>
            {error && (
              <Alert
                className={cls.notice}
                showIcon
                type='error'
                message={error}
                action={
                  <Button size='small' onClick={() => loadRoster(true)}>
                    Retry
                  </Button>
                }
              />
            )}
            <Space
              className='w-100 justify-content-between'
              style={{ marginBottom: 16 }}
              wrap
            >
              <Input
                allowClear
                prefix={<SearchOutlined />}
                placeholder='Search this page'
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                style={{ maxWidth: 360 }}
                aria-label='Search delivery drivers'
              />
              <Typography.Text type='secondary'>
                {meta?.total ?? rows.length} shop relationship
                {(meta?.total ?? rows.length) === 1 ? '' : 's'}
              </Typography.Text>
            </Space>
            {loading ? (
              <div className={cls.roster} aria-label='Loading drivers'>
                <Skeleton active paragraph={{ rows: 2 }} />
                <Skeleton active paragraph={{ rows: 2 }} />
              </div>
            ) : visibleRows.length === 0 ? (
              <div className={cls.empty}>
                {rows.length ? (
                  <Empty
                    image={Empty.PRESENTED_IMAGE_SIMPLE}
                    description='No drivers match that search.'
                  />
                ) : (
                  <>
                    <p className={cls.emptyTitle}>A roster starts with an invitation</p>
                    <p className={cls.emptyCopy}>
                      Invite with an email address. The driver creates or signs into
                      their own account, verifies that email, then chooses whether
                      to accept this shop relationship.
                    </p>
                    {canInvite && (
                      <Button
                        type='primary'
                        icon={<PlusOutlined />}
                        style={{ marginTop: 18 }}
                        onClick={() =>
                          navigate('/seller/invitations/deliverymen/add')
                        }
                      >
                        Invite a driver
                      </Button>
                    )}
                  </>
                )}
              </div>
            ) : (
              <div className={cls.roster}>
                {visibleRows.map((row) => {
                  const displayName = [
                    row?.user?.firstname || row?.firstname,
                    row?.user?.lastname || row?.lastname,
                  ]
                    .filter(Boolean)
                    .join(' ');
                  const globallyInactive = row?.user && row.user.active === false;
                  return (
                    <article className={cls.driverRow} key={row.id}>
                      <div className={cls.identity}>
                        <div className={cls.name}>
                          {displayName || 'Driver invitation'}
                        </div>
                        <div className={cls.email}>{row.email || 'Email unavailable'}</div>
                      </div>
                      <div>
                        <span className={cls.fieldLabel}>Shop relationship</span>
                        <span className={stateClass(row.state)}>{row.state}</span>
                      </div>
                      <div>
                        <span className={cls.fieldLabel}>Invitation / delivery</span>
                        <div className={cls.value}>
                          {row.notification_status || 'Not provided'}
                          {row.expires_at && row.state === 'pending' && (
                            <div style={{ color: '#748078', fontSize: 11 }}>
                              Expires {new Date(row.expires_at).toLocaleDateString()}
                            </div>
                          )}
                        </div>
                      </div>
                      {renderActions(row)}
                      {freshLinks[row.id] && (
                        <div style={{ gridColumn: '1 / -1' }}>
                          <span className={cls.fieldLabel}>
                            New link from this resend response
                          </span>
                          <Input.TextArea
                            readOnly
                            value={freshLinks[row.id]}
                            autoSize={{ minRows: 2, maxRows: 3 }}
                            aria-label={`New invitation link for ${row.email}`}
                          />
                          <Button
                            size='small'
                            icon={<CopyOutlined />}
                            style={{ marginTop: 7 }}
                            onClick={() => copyFreshLink(row.id)}
                          >
                            Copy resent invitation link
                          </Button>
                        </div>
                      )}
                      {row.eligible === false && (
                        <div style={{ gridColumn: '1 / -1' }}>
                          <Tag color='gold'>Account not currently eligible</Tag>
                          <Typography.Text type='secondary'>
                            Eligibility reflects the driver account's global
                            availability, not this shop's relationship status.
                          </Typography.Text>
                        </div>
                      )}
                      {globallyInactive && (
                        <Alert
                          style={{ gridColumn: '1 / -1', margin: 0 }}
                          type='warning'
                          showIcon
                          message='This driver account is globally inactive.'
                          description='Shop access is separate from account availability. Contact support or the account holder; changing this shop relationship will not reactivate the global account.'
                        />
                      )}
                      {row.state === 'conflict' && (
                        <Alert
                          style={{ gridColumn: '1 / -1', margin: 0 }}
                          type='warning'
                          showIcon
                          message='This invitation needs review'
                          description='The email is associated with an account that cannot be linked to this shop right now. No account or shop membership was changed.'
                        />
                      )}
                    </article>
                  );
                })}
              </div>
            )}
            {!loading && rows.length > 0 && lastPage > 1 && (
              <Space
                className='w-100 justify-content-between'
                style={{ marginTop: 18 }}
              >
                <Typography.Text type='secondary'>
                  Page {meta?.current_page || currentPage} of {lastPage}
                </Typography.Text>
                <Space>
                  <Button
                    icon={<LeftOutlined />}
                    disabled={Number(meta?.current_page || currentPage) <= 1}
                    onClick={() =>
                      setCurrentPage((page) => Math.max(1, page - 1))
                    }
                  >
                    Previous
                  </Button>
                  <Button
                    icon={<RightOutlined />}
                    disabled={
                      Number(meta?.current_page || currentPage) >= lastPage
                    }
                    onClick={() =>
                      setCurrentPage((page) => Math.min(lastPage, page + 1))
                    }
                  >
                    Next
                  </Button>
                </Space>
              </Space>
            )}
          </>
        )}
      </Card>
    </div>
  );
}

export default SellerDeliverymen;