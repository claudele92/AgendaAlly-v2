import UserForm from './user-form';
import { Tabs } from 'antd';
import { useTranslation } from 'react-i18next';
import { useParams } from 'react-router-dom';
import MastersWorkingAndClosedDays from './working-and-closed-days';
import DisabledTimes from './disabled-times';
import MasterBranch from './master-branch';

const { TabPane } = Tabs;

const FormDeliveryman = ({ form, handleSubmit, masterId }) => {
  const { t } = useTranslation();
  const { uuid } = useParams();
  return (
    <>
      {uuid === undefined ? (
        <UserForm form={form} handleSubmit={handleSubmit} />
      ) : (
        <Tabs tabPosition='left' size='small'>
          <TabPane key='user' tab={t('user')}>
            <UserForm form={form} handleSubmit={handleSubmit} />
          </TabPane>
          <TabPane key='closed_days' tab={t('working.and.closed.days')}>
            <MastersWorkingAndClosedDays />
          </TabPane>
          <TabPane key='disabled_times' tab={t('disabled.times')}>
            <DisabledTimes />
          </TabPane>
          <TabPane key='branch' tab={t('assign.to.branch')}>
            <MasterBranch masterId={masterId} />
          </TabPane>
        </Tabs>
      )}
    </>
  );
};

export default FormDeliveryman;
