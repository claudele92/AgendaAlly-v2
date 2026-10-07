import React, { useContext } from 'react';
import { Drawer } from 'antd';
import { BookingContext } from '../provider';
import { useNavigate } from 'react-router-dom';
const DrawerView = ({ children, open, setOpen, footer }) => {
  const navigate = useNavigate();
  const { setCalculatedData } = useContext(BookingContext);
  const onClose = () => {
    setOpen('');
    navigate('/calendar');
    setCalculatedData({});
  };
  return (
    <Drawer
      onClose={onClose}
      visible={open}
      className='drawer-view'
      width='min(600px, calc(100vw - 16px))'
      headerStyle={{ display: 'none' }}
      footer={footer}
      destroyOnClose
    >
      {children}
    </Drawer>
  );
};
export default DrawerView;
