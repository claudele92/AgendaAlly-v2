import { Form } from 'antd';
import React, { useEffect, useMemo, useState } from 'react';
import { createContext } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useSelector } from 'react-redux';
import userService from 'services/seller/user';
import { useNavigationScope } from 'context/navigation-scope';
import { getVerifiedCalendarShopId } from 'views/calendar/helpers/calendar-context.mjs';
import {
  clearClientSaveIntent,
  clientSaveIntentStorage,
  clientSaveIntentStorageKey,
  readClientSaveIntent,
} from './helpers/client-save-intent.mjs';
import { bookingClientFromCreateResponse } from './helpers/booking-client-response.mjs';
export const BookingContext = createContext(null);

const BookingContextProvider = ({ children }) => {
  let [params] = useSearchParams();
  const service_id = params.get('service_id');
  const disabled_slot_id = params.get('disabled_slot_id');

  const [serviceForm] = Form.useForm();
  const [infoForm] = Form.useForm();
  const [blocktimeForm] = Form.useForm();
  const [serviceData, setServiceData] = useState([]);
  const [calculatedData, setCalculatedData] = useState({});
  const [infoData, setInfoData] = useState({});
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedSlots, setSelectedSlots] = useState(null);
  const [isLoalding, setIsLoading] = useState(false);
  const [viewContent, setViewContent] = useState('');
  const [isAddForm, setIsAddForm] = useState(false);
  const [formDetailId, setFormDetailId] = useState(null);
  const [updateStatus, setUpdateStatus] = useState(null);
  const [selectedService, setSelectedService] = useState(null);
  const [savedBookingId, setSavedBookingId] = useState(null);
  const { user } = useSelector((state) => state.auth);
  const navigationScope = useNavigationScope();
  const verifiedShopId = getVerifiedCalendarShopId(user, navigationScope);
  const intentStorageKey = useMemo(
    () => clientSaveIntentStorageKey(user?.id, verifiedShopId),
    [user?.id, verifiedShopId],
  );
  const [clientSaveIntent, setClientSaveIntent] = useState(null);
  const [clientSaveIntentRecoveryError, setClientSaveIntentRecoveryError] = useState('');

  useEffect(() => {
    if (!intentStorageKey || !verifiedShopId || !user?.id) {
      setClientSaveIntent(null);
      setClientSaveIntentRecoveryError('');
      return undefined;
    }
    const storage = clientSaveIntentStorage();
    const intentId = readClientSaveIntent(storage, intentStorageKey);
    if (!intentId) {
      setClientSaveIntent(null);
      setClientSaveIntentRecoveryError('');
      return undefined;
    }
    let current = true;
    setClientSaveIntent({ intentId, state: 'recovering', payload: null });
    setClientSaveIntentRecoveryError('');
    userService.bookingClientSaveIntentStatus(intentId).then((response) => {
      if (!current) return;
      const status = response || {};
      const { client, reusedExisting } = bookingClientFromCreateResponse(status);
      if (status.save_status === 'resolved' && client?.id && client?.kind && client?.name) {
        clearClientSaveIntent(storage, intentStorageKey, intentId);
        setClientSaveIntent({
          intentId,
          state: 'resolved',
          recovered: true,
          client,
          reusedExisting,
          payload: null,
        });
        return;
      }
      if (Number(response?.status) === 404 || status.save_status === 'not_found') {
        setClientSaveIntent({ intentId, state: 'reentry', payload: null });
        setClientSaveIntentRecoveryError(
          'No receipt was found; the save may still be in flight. Re-enter the original details and retry this same save reference.',
        );
        return;
      }
      setClientSaveIntent({ intentId, state: 'needs-recovery', payload: null });
      setClientSaveIntentRecoveryError(
        'A previous client save still needs confirmation. This device did not retain client details. Resolve the same save before starting another.',
      );
    }).catch((error) => {
      if (!current) return;
      if (Number(error?.response?.status) === 404) {
        setClientSaveIntent({ intentId, state: 'reentry', payload: null });
        setClientSaveIntentRecoveryError(
          'No receipt was found; the save may still be in flight. Re-enter the original details and retry this same save reference.',
        );
        return;
      }
      if (Number(error?.response?.status) === 409) {
        setClientSaveIntent({ intentId, state: 'conflict', payload: null });
        setClientSaveIntentRecoveryError(
          'This save reference has a payload conflict or unavailable result. It cannot create another client; contact your Shop administrator to resolve it.',
        );
        return;
      }
      setClientSaveIntent({ intentId, state: 'needs-recovery', payload: null });
      setClientSaveIntentRecoveryError(
        'Unable to confirm the previous client save. Retry the status check before starting another save.',
      );
    });
    return () => { current = false; };
  }, [intentStorageKey, verifiedShopId, user?.id]);

  const sumInterval = calculatedData?.items?.reduce(
    (acc, curr) => acc + curr?.service_master?.interval,
    0,
  );

  return (
    <BookingContext.Provider
      value={{
        infoForm,
        serviceForm,
        serviceData,
        setServiceData,
        calculatedData,
        setCalculatedData,
        selectedSlots,
        setSelectedSlots,
        isModalOpen,
        setIsModalOpen,
        isLoalding,
        setIsLoading,
        viewContent,
        setViewContent,
        sumInterval,
        infoData,
        setInfoData,
        service_id,
        isAddForm,
        setIsAddForm,
        disabled_slot_id,
        blocktimeForm,
        formDetailId,
        setFormDetailId,
        updateStatus,
        setUpdateStatus,
        selectedService,
        setSelectedService,
        savedBookingId,
        setSavedBookingId,
         clientSaveIntent,
         setClientSaveIntent,
         clientSaveIntentScope: { actorId: user?.id, shopId: verifiedShopId, storageKey: intentStorageKey },
         clientSaveIntentRecoveryError,
         setClientSaveIntentRecoveryError,
      }}
    >
      {children}
    </BookingContext.Provider>
  );
};

export default BookingContextProvider;
