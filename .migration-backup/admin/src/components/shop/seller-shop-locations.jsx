import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { Button, Col, Image, Modal, Space, Table } from 'antd';
import { useContext, useEffect, useState } from 'react';
import { fetchSellerShopLocations } from 'redux/slices/shop-locations';
import { DeleteOutlined, EditOutlined } from '@ant-design/icons';
import LocationSelect from './location-select';
import shopLocationsService from 'services/seller/shop-locations';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import { disableRefetch } from 'redux/slices/menu';
import { useQueryParams } from 'helpers/useQueryParams';

const ShopLocations = ({ next, prev, locationType }) => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const queryParams = useQueryParams();

  const { locations, loading } = useSelector((state) => state.shopLocations);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const [ids, setIds] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [isLocationSelectModalOpen, setIsLocationSelectModalOpen] =
    useState(false);
  const [editingLocation, setEditingLocation] = useState(null);
  const { setIsModalVisible } = useContext(Context);

  const columns = [
    {
      title: t('location'),
      dataIndex: 'location',
      key: 'location',
      render: (_, row) => (
        <span>
          {row?.alias ? `${row.alias} — ` : ''}
          {row?.country?.translation?.title}
          {row?.city ? ',' : ''} {row?.city?.translation?.title}
        </span>
      ),
    },
    {
      title: t('address'),
      dataIndex: 'address',
      key: 'address',
    },
    {
      title: t('image'),
      dataIndex: 'country',
      key: 'image',
      render: (country) => (
        <Image
          src={country?.img}
          alt={country?.translation?.title}
          width={60}
          height={40}
        />
      ),
    },

    {
      title: t('options'),
      dataIndex: 'options',
      key: 'options',
      render: (_, row) => (
        <Space>
          <Button
            onClick={() => {
              setEditingLocation(row);
              setIsLocationSelectModalOpen(true);
            }}
            icon={<EditOutlined />}
          />
          <Button
            onClick={() => {
              setIds([row.id]);
              setIsModalVisible(true);
            }}
            type='primary'
            danger
            icon={<DeleteOutlined />}
          />
        </Space>
      ),
    },
  ];

  const params = {
    shop_id: activeMenu?.data?.id,
    type: locationType?.value,
  };
  const locationDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...ids.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
      type: locationType?.value,
    };
    shopLocationsService
      .delete(params)
      .then(() => {
        dispatch(fetchSellerShopLocations(params));
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setLoadingBtn(false);
        setIds(null);
        setIsModalVisible(false);
      });
  };

  const closeLocationSelectModal = () => {
    setIsLocationSelectModalOpen(false);
    setEditingLocation(null);
  };

  // Builds the {value, label} shape InfiniteSelect (labelInValue) needs to
  // display country/city without re-fetching them first, from the row's
  // already-loaded ShopLocationResource relations.
  const buildInitialValues = (row) => ({
    country: row?.country
      ? {
          value: `${row.country.id},${row.country.region_id}`,
          label: row.country?.translation?.title,
          key: row.country.id,
        }
      : undefined,
    city: row?.city
      ? { value: row.city.id, label: row.city?.translation?.title }
      : { value: 'all', label: t('whole.country') },
    alias: row?.alias,
    address: row?.address,
    latitude: row?.latitude,
    longitude: row?.longitude,
  });

  const handleAddLocation = (values) => {
    const country = values.country.value.split(',')[0];
    const region = values.country.value.split(',')[1];
    const body = {
      country_id: country,
      region_id: region,
      city_id: values.city?.value,
      alias: values.alias,
      address: values.address,
      latitude: values.latitude,
      longitude: values.longitude,
      shop_id: activeMenu?.data?.id,
      type: locationType?.value,
    };
    setLoadingBtn(true);
    const request = editingLocation
      ? shopLocationsService.update(editingLocation.id, body)
      : shopLocationsService.create(body);
    request
      .then(() => {
        toast.success(
          editingLocation ? t('successfully.updated') : t('successfully.added'),
        );
        dispatch(fetchSellerShopLocations(params));
        closeLocationSelectModal();
      })
      .finally(() => {
        setLoadingBtn(false);
      });
  };

  useEffect(() => {
    dispatch(fetchSellerShopLocations(params));
    dispatch(disableRefetch(activeMenu));
  }, [queryParams.values?.step]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerShopLocations(params));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  return (
    <>
      <Space className='justify-content-end w-100'>
        <Button
          onClick={() => setIsLocationSelectModalOpen(true)}
          type='primary'
        >
          {t('add')}
        </Button>
      </Space>
      <Table loading={loading} dataSource={locations} columns={columns} />
      <Modal
        footer={null}
        visible={isLocationSelectModalOpen}
        destroyOnClose
        onCancel={closeLocationSelectModal}
      >
        <LocationSelect
          onClose={closeLocationSelectModal}
          onSubmit={handleAddLocation}
          isButtonLoading={loadingBtn}
          initialValues={editingLocation ? buildInitialValues(editingLocation) : undefined}
        />
      </Modal>
      <CustomModal
        click={locationDelete}
        text={t('delete.location')}
        setText={setIds}
        loading={loadingBtn}
      />
      <Col span={24}>
        <Space>
          <Button type='primary' htmlType='button' onClick={() => next()}>
            {t('next')}
          </Button>
          <Button htmlType='button' onClick={() => prev()}>
            {t('prev')}
          </Button>
        </Space>
      </Col>
    </>
  );
};

export default ShopLocations;
