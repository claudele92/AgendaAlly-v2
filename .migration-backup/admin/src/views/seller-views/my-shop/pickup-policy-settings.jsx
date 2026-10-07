import React, { useEffect, useMemo, useState } from 'react';
import {
  Alert,
  Button,
  Card,
  Col,
  Input,
  InputNumber,
  Popconfirm,
  Radio,
  Row,
  Select,
  Skeleton,
  Space,
  Tag,
  Typography,
} from 'antd';
import { PlusOutlined, ReloadOutlined, SaveOutlined } from '@ant-design/icons';
import { useTranslation } from 'react-i18next';
import sellerShopLocationService from 'services/seller/shop-locations';
import pickupPolicyService from 'services/seller/pickup-policy';

const weekdayLabels = [
  'Sunday',
  'Monday',
  'Tuesday',
  'Wednesday',
  'Thursday',
  'Friday',
  'Saturday',
];
const commonZones = [
  'Africa/Douala',
  'Africa/Abidjan',
  'Africa/Lagos',
  'Africa/Nairobi',
  'Africa/Johannesburg',
  'America/New_York',
  'America/Chicago',
  'America/Los_Angeles',
  'America/Toronto',
  'Europe/London',
  'Europe/Paris',
  'Europe/Berlin',
  'Asia/Dubai',
  'Asia/Kolkata',
  'Asia/Singapore',
  'Asia/Tokyo',
  'Australia/Sydney',
];
const availableTimeZones = () => {
  try {
    return Intl.supportedValuesOf
      ? Intl.supportedValuesOf('timeZone')
      : commonZones;
  } catch {
    return commonZones;
  }
};
const defaultHours = () =>
  weekdayLabels.map((_, day) => ({
    day,
    enabled: day !== 0,
    start_time: '09:00',
    end_time: day === 6 ? '16:00' : '18:00',
  }));
const defaultPolicy = () => ({
  timing_mode: 'as_soon_as_ready',
  timezone: '',
  weekly_hours: defaultHours(),
  preparation_minutes: 60,
  window_minutes: 120,
  same_day: true,
  cutoff: '15:00',
  blackout_dates: [],
});
const unwrap = (response) => response?.data?.data || response?.data || response;
const normalizePolicy = (value) => {
  const defaults = defaultPolicy();
  if (!value) return defaults;
  const storedHours = Array.isArray(value.weekly_hours) ? value.weekly_hours : [];
  const hours = defaults.weekly_hours.map((fallback) => {
    const stored = storedHours.find(
      (entry) => Number(entry.day) === fallback.day,
    );
    return stored
      ? {
          ...fallback,
          ...stored,
          enabled: stored.enabled ?? stored.is_open ?? true,
          start_time: stored.start || stored.start_time || fallback.start_time,
          end_time: stored.end || stored.end_time || fallback.end_time,
        }
      : fallback;
  });
  return {
    ...defaults,
    ...value,
    window_minutes: value.window_minutes ?? defaults.window_minutes,
    same_day: value.same_day ?? defaults.same_day,
    cutoff: value.cutoff || defaults.cutoff,
    weekly_hours: hours,
    blackout_dates: Array.isArray(value.blackout_dates)
      ? value.blackout_dates
      : [],
  };
};

const PickupPolicySettings = ({ shopId }) => {
  const { t } = useTranslation();
  const [branches, setBranches] = useState([]);
  const [branchId, setBranchId] = useState();
  const [policy, setPolicy] = useState(defaultPolicy);
  const [branchesLoading, setBranchesLoading] = useState(true);
  const [policyLoading, setPolicyLoading] = useState(false);
  const [policyLoadFailed, setPolicyLoadFailed] = useState(false);
  const [saving, setSaving] = useState(false);
  const [resetting, setResetting] = useState(false);
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [blackoutDate, setBlackoutDate] = useState('');
  const timeZones = useMemo(() => availableTimeZones(), []);

  const loadBranches = () => {
    setBranchesLoading(true);
    setError('');
    sellerShopLocationService
      .getAll({ shop_id: shopId, location_type: 1, perPage: 100 })
      .then((response) => {
        const items = unwrap(response);
        const productBranches = (Array.isArray(items) ? items : [])
          .filter((branch) => Number(branch.type) === 1)
          .sort((a, b) => Number(a.id) - Number(b.id));
        setBranches(productBranches);
        setBranchId((current) =>
          productBranches.some((branch) => Number(branch.id) === Number(current))
            ? current
            : productBranches[0]?.id,
        );
      })
      .catch((err) => setError(err?.message || t('pickup.branches.load.error')))
      .finally(() => setBranchesLoading(false));
  };

  const loadPolicy = (id) => {
    if (!id) return;
    setPolicyLoading(true);
    setPolicyLoadFailed(false);
    setPolicy(defaultPolicy());
    setError('');
    pickupPolicyService
      .get(shopId, id)
      .then((response) => setPolicy(normalizePolicy(unwrap(response))))
      .catch((err) => {
        setPolicyLoadFailed(true);
        setPolicy(defaultPolicy());
        setError(
          err?.response?.status === 404
            ? t('pickup.policy.unavailable', {
                defaultValue: 'Pickup settings could not be loaded. Try again before saving.',
              })
            : err?.message || t('pickup.policy.load.error'),
        );
      })
      .finally(() => setPolicyLoading(false));
  };

  useEffect(() => {
    if (shopId) loadBranches();
  }, [shopId]);

  useEffect(() => {
    if (branchId) loadPolicy(branchId);
  }, [branchId, shopId]);

  const selectedBranch = branches.find(
    (branch) => Number(branch.id) === Number(branchId),
  );
  const updatePolicy = (key, value) =>
    setPolicy((current) => ({ ...current, [key]: value }));
  const updateDay = (day, key, value) =>
    setPolicy((current) => ({
      ...current,
      weekly_hours: current.weekly_hours.map((hours) =>
        hours.day === day ? { ...hours, [key]: value } : hours,
      ),
    }));

  const savePolicy = () => {
    if (!branchId) return;
    if (
      policy.timing_mode === 'scheduled' &&
      !policy.timezone
    ) {
      setError(
        t('pickup.timezone.required', {
          defaultValue: 'Choose the branch timezone before enabling scheduled pickup.',
        }),
      );
      return;
    }
    const invalidHours = policy.weekly_hours.some(
      (day) =>
        day.enabled &&
        (!day.start_time ||
          !day.end_time ||
          day.start_time >= day.end_time),
    );
    if (policy.timing_mode === 'scheduled' && invalidHours) {
      setError(
        t('pickup.hours.invalid', {
          defaultValue: 'Each open day needs a valid start and end time.',
        }),
      );
      return;
    }
    setSaving(true);
    setError('');
    setSuccessMessage('');
    const body = {
      timing_mode: policy.timing_mode,
      timezone: policy.timezone || null,
      weekly_hours: policy.weekly_hours.map((day) => ({
        day: day.day,
        enabled: !!day.enabled,
        start: day.start_time,
        end: day.end_time,
      })),
      preparation_minutes: Number(policy.preparation_minutes),
      window_minutes: Number(policy.window_minutes),
      same_day: !!policy.same_day,
      cutoff: policy.cutoff,
      blackout_dates: policy.blackout_dates,
    };
    pickupPolicyService
      .update(shopId, branchId, body)
      .then((response) => {
        setPolicy(normalizePolicy(unwrap(response) || body));
        setSuccessMessage(
          t('pickup.policy.saved', {
            defaultValue: 'Pickup schedule saved for this branch.',
          }),
        );
      })
      .catch((err) => setError(err?.message || t('pickup.policy.save.error')))
      .finally(() => setSaving(false));
  };

  const resetPolicy = () => {
    if (!branchId) return;
    setResetting(true);
    setError('');
    setSuccessMessage('');
    pickupPolicyService
      .reset(shopId, branchId)
      .then(() => {
        setPolicy(defaultPolicy());
        setSuccessMessage(
          t('pickup.policy.reset', {
            defaultValue: 'Pickup timing reset to ASAP for this branch.',
          }),
        );
      })
      .catch((err) => setError(err?.message || t('pickup.policy.reset.error')))
      .finally(() => setResetting(false));
  };

  const addBlackoutDate = () => {
    if (!blackoutDate || policy.blackout_dates.includes(blackoutDate)) return;
    updatePolicy(
      'blackout_dates',
      [...policy.blackout_dates, blackoutDate].sort(),
    );
    setBlackoutDate('');
  };

  if (!shopId) {
    return (
      <Alert
        type='info'
        showIcon
        message={t('pickup.policy.shop.required', {
          defaultValue: 'Save this Shop before configuring branch pickup schedules.',
        })}
      />
    );
  }

  return (
    <Card
      size='small'
      title={t('pickup.scheduling.title', {
        defaultValue: 'Shop Pickup scheduling',
      })}
      extra={
        <Tag color='gold'>
          {t('pickup.policy.branch.scoped', {
            defaultValue: 'Per Product branch',
          })}
        </Tag>
      }
    >
      <Typography.Paragraph type='secondary'>
        {t('pickup.policy.description', {
          defaultValue:
            'Pickup stays optional. These settings apply only to the selected Product branch.',
        })}
      </Typography.Paragraph>

      {error && (
        <Alert
          className='mb-3'
          type='error'
          showIcon
          message={error}
          action={
            <Button
              size='small'
              icon={<ReloadOutlined />}
              onClick={() => (branchId ? loadPolicy(branchId) : loadBranches())}
            >
              {t('retry', { defaultValue: 'Retry' })}
            </Button>
          }
        />
      )}
      {successMessage && (
        <Alert
          className='mb-3'
          type='success'
          showIcon
          message={successMessage}
          closable
          onClose={() => setSuccessMessage('')}
        />
      )}

      {(branchesLoading || policyLoading) ? (
        <Skeleton active paragraph={{ rows: 4 }} />
      ) : branches.length === 0 ? (
          <Alert
            type='info'
            showIcon
            message={t('pickup.no.product.branches', {
              defaultValue:
                'Add a Product branch before configuring pickup scheduling.',
            })}
          />
        ) : (
          <>
            <Row gutter={[12, 12]}>
              <Col xs={24} md={16}>
                <Typography.Text strong>
                  {t('pickup.branch', { defaultValue: 'Pickup branch' })}
                </Typography.Text>
                <Select
                  className='w-100 mt-1'
                  value={branchId}
                  onChange={(value) => {
                    setBranchId(value);
                    setSuccessMessage('');
                  }}
                  placeholder={t('choose.pickup.branch', {
                    defaultValue: 'Choose a Product branch',
                  })}
                  options={branches.map((branch) => ({
                    value: branch.id,
                    label: [
                      branch.alias,
                      branch.address,
                      branch.city?.translation?.title,
                    ]
                      .filter(Boolean)
                      .join(' · ') || `Branch ${branch.id}`,
                  }))}
                />
              </Col>
              <Col xs={24} md={8}>
                <Typography.Text strong>
                  {t('pickup.timing', { defaultValue: 'Pickup timing' })}
                </Typography.Text>
                <Radio.Group
                  className='mt-2 d-flex flex-column'
                  value={policy.timing_mode}
                  onChange={(event) =>
                    updatePolicy('timing_mode', event.target.value)
                  }
                >
                  <Radio value='as_soon_as_ready'>
                    {t('pickup.asap', {
                      defaultValue: 'As soon as order is ready',
                    })}
                  </Radio>
                  <Radio value='scheduled'>
                    {t('pickup.scheduled', {
                      defaultValue: 'Customers choose a pickup window',
                    })}
                  </Radio>
                </Radio.Group>
              </Col>
            </Row>

            {policy.timing_mode === 'scheduled' && (
              <div className='mt-4'>
                <Row gutter={[12, 12]}>
                  <Col xs={24} md={12}>
                    <Typography.Text strong>
                      {t('pickup.timezone', { defaultValue: 'Branch timezone' })}
                    </Typography.Text>
                    <Select
                      className='w-100 mt-1'
                      showSearch
                      value={policy.timezone || undefined}
                      placeholder={t('pickup.choose.timezone', {
                        defaultValue: 'Choose an IANA timezone',
                      })}
                      optionFilterProp='label'
                      onChange={(value) => updatePolicy('timezone', value)}
                      options={timeZones.map((zone) => ({
                        value: zone,
                        label: zone,
                      }))}
                    />
                    <Typography.Paragraph type='secondary' className='mt-1 mb-0'>
                      {selectedBranch?.address || ''}
                      {selectedBranch?.alias ? ` · ${selectedBranch.alias}` : ''}
                    </Typography.Paragraph>
                  </Col>
                  <Col xs={12} md={6}>
                    <Typography.Text strong>
                      {t('pickup.preparation.time', {
                        defaultValue: 'Preparation time (minutes)',
                      })}
                    </Typography.Text>
                    <InputNumber
                      className='w-100 mt-1'
                      min={0}
                      max={10080}
                      value={policy.preparation_minutes}
                      onChange={(value) =>
                        updatePolicy('preparation_minutes', value || 0)
                      }
                    />
                  </Col>
                  <Col xs={12} md={6}>
                    <Typography.Text strong>
                      {t('pickup.window.duration', {
                        defaultValue: 'Pickup window',
                      })}
                    </Typography.Text>
                    <Select
                      className='w-100 mt-1'
                      value={policy.window_minutes}
                      onChange={(value) =>
                        updatePolicy('window_minutes', value)
                      }
                      options={[
                        { value: 30, label: '30 minutes' },
                        { value: 60, label: '1 hour' },
                        { value: 90, label: '90 minutes' },
                        { value: 120, label: '2 hours' },
                        { value: 180, label: '3 hours' },
                        { value: 240, label: '4 hours' },
                      ]}
                    />
                  </Col>
                </Row>

                <Typography.Title level={5} className='mt-4 mb-2'>
                  {t('pickup.availability', {
                    defaultValue: 'Weekly pickup availability',
                  })}
                </Typography.Title>
                <div className='d-flex flex-column' style={{ gap: 8 }}>
                  {policy.weekly_hours.map((day) => (
                    <Row
                      key={day.day}
                      align='middle'
                      gutter={[8, 8]}
                      className='border rounded p-2 mx-0'
                    >
                      <Col xs={24} sm={8}>
                        <label className='d-flex align-items-center' style={{ gap: 8 }}>
                          <input
                            type='checkbox'
                            checked={!!day.enabled}
                            onChange={(event) =>
                              updateDay(
                                day.day,
                                'enabled',
                                event.target.checked,
                              )
                            }
                          />
                          {t(`day.${day.day}`, {
                            defaultValue: weekdayLabels[day.day],
                          })}
                        </label>
                      </Col>
                      <Col xs={12} sm={8}>
                        <Input
                          aria-label={`${weekdayLabels[day.day]} start`}
                          type='time'
                          disabled={!day.enabled}
                          value={day.start_time}
                          onChange={(event) =>
                            updateDay(
                              day.day,
                              'start_time',
                              event.target.value,
                            )
                          }
                        />
                      </Col>
                      <Col xs={12} sm={8}>
                        <Input
                          aria-label={`${weekdayLabels[day.day]} end`}
                          type='time'
                          disabled={!day.enabled}
                          value={day.end_time}
                          onChange={(event) =>
                            updateDay(
                              day.day,
                              'end_time',
                              event.target.value,
                            )
                          }
                        />
                      </Col>
                    </Row>
                  ))}
                </div>

                <Row gutter={[12, 12]} className='mt-4'>
                  <Col xs={24} md={8}>
                    <label className='d-flex align-items-center' style={{ gap: 8 }}>
                      <input
                        type='checkbox'
                        checked={!!policy.same_day}
                        onChange={(event) =>
                          updatePolicy('same_day', event.target.checked)
                        }
                      />
                      {t('pickup.same.day.enabled', {
                        defaultValue: 'Allow same-day pickup',
                      })}
                    </label>
                  </Col>
                  <Col xs={24} md={8}>
                    <Typography.Text strong>
                      {t('pickup.same.day.cutoff', {
                        defaultValue: 'Same-day cutoff',
                      })}
                    </Typography.Text>
                    <Input
                      className='mt-1'
                      type='time'
                      disabled={!policy.same_day}
                      value={policy.cutoff}
                      onChange={(event) =>
                        updatePolicy('cutoff', event.target.value)
                      }
                    />
                  </Col>
                  <Col xs={24}>
                    <Typography.Text strong>
                      {t('pickup.blackout.dates', {
                        defaultValue: 'Blackout dates',
                      })}
                    </Typography.Text>
                    <Space className='mt-1' wrap>
                      <Input
                        type='date'
                        value={blackoutDate}
                        onChange={(event) => setBlackoutDate(event.target.value)}
                      />
                      <Button
                        icon={<PlusOutlined />}
                        onClick={addBlackoutDate}
                        disabled={!blackoutDate}
                      >
                        {t('add.date', { defaultValue: 'Add date' })}
                      </Button>
                      {policy.blackout_dates.map((date) => (
                        <Tag
                          key={date}
                          closable
                          onClose={() =>
                            updatePolicy(
                              'blackout_dates',
                              policy.blackout_dates.filter((item) => item !== date),
                            )
                          }
                        >
                          {date}
                        </Tag>
                      ))}
                    </Space>
                  </Col>
                </Row>
              </div>
            )}

            <div className='d-flex justify-content-between mt-4'>
              <Popconfirm
                title={t('pickup.reset.confirm', {
                  defaultValue:
                    'Reset this branch to pickup as soon as the order is ready?',
                })}
                onConfirm={resetPolicy}
                okText={t('reset')}
                cancelText={t('cancel')}
              >
                <Button
                  icon={<ReloadOutlined />}
                  loading={resetting}
                  disabled={!branchId || policyLoading || policyLoadFailed}
                >
                  {t('reset.to.asap', { defaultValue: 'Reset to ASAP' })}
                </Button>
              </Popconfirm>
              <Button
                type='primary'
                icon={<SaveOutlined />}
                loading={saving}
                disabled={!branchId || policyLoading || policyLoadFailed}
                onClick={savePolicy}
              >
                {t('save')}
              </Button>
            </div>
            <div className='mt-2 text-muted'>
              {t('pickup.capacity.unlimited', {
                defaultValue: 'Pickup windows have no order-capacity limit. They are collection times, not limited reservations.',
              })}
            </div>
          </>
        )}
    </Card>
  );
};

export default PickupPolicySettings;