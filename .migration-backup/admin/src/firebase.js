import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import {
  getFirestore,
  collection,
  onSnapshot,
  query,
  orderBy,
  addDoc,
  serverTimestamp,
  updateDoc,
  doc,
  deleteDoc,
  where,
  writeBatch,
} from 'firebase/firestore';
import { batch as reduxBatch } from 'react-redux';
import {
  API_KEY,
  APP_ID,
  AUTH_DOMAIN,
  MEASUREMENT_ID,
  MESSAGING_SENDER_ID,
  PROJECT_ID,
  STORAGE_BUCKET,
  VAPID_KEY,
  ADMIN_RUNTIME_CONFIG_VALID,
} from './configs/app-global';
import { store } from './redux/store';
import {
  setMessages,
  setMessagesLoading,
  setUserIds,
} from './redux/slices/chat';
import { toast } from 'react-toastify';
import userService from './services/seller/user';
import { setFirebaseToken } from './redux/slices/auth';
import { isFirebaseConfigured } from './configs/runtime-config.mjs';

const firebaseConfigured =
  ADMIN_RUNTIME_CONFIG_VALID && isFirebaseConfigured(import.meta.env);
const firebaseConfig = {
  apiKey: API_KEY,
  authDomain: AUTH_DOMAIN,
  projectId: PROJECT_ID,
  storageBucket: STORAGE_BUCKET,
  messagingSenderId: MESSAGING_SENDER_ID,
  appId: APP_ID,
  ...(MEASUREMENT_ID ? { measurementId: MEASUREMENT_ID } : {}),
};

// Optional Firebase services are only initialized after explicit opt-in with
// complete environment-scoped credentials. No placeholder config is created.
const app = firebaseConfigured ? initializeApp(firebaseConfig) : null;
const messaging = app ? getMessaging(app) : null;
const db = app ? getFirestore(app) : null;
export const pushMessagingEnabled = Boolean(messaging);

const requireDatabase = () => {
  if (!db) {
    throw new Error(
      'Firebase chat is disabled for this environment. Explicitly enable and configure VITE_FIREBASE_ENABLED to use it.',
    );
  }
  return db;
};

const reportFirebaseError = (error) => {
  console.error(error);
  toast.error(error?.message || String(error));
};

export const firebaseChatList = [];

export function buildChatList(userDataList, firebaseChatList) {
  // reverse array for searching from end to beginning;
  firebaseChatList.reverse();

  return userDataList?.data?.map((userDataItem) => {
    const chatItem = firebaseChatList.find((item) =>
      item.ids.includes(userDataItem.id),
    );
    return { ...chatItem, user: userDataItem };
  });
}

export function getChat(currentUserId) {
  try {
    const database = requireDatabase();
    const chatCollectionRef = collection(database, 'chat');
    const chatQuery = query(
      chatCollectionRef,
      where('ids', 'array-contains', currentUserId),
      orderBy('time', 'asc'),
    );

    return onSnapshot(chatQuery, (chatSnapshot) => {
      const firebaseChats = chatSnapshot.docs.map((doc) => ({
        chatId: doc.id,
        ...doc.data(),
      }));

      firebaseChatList.push(...firebaseChats);

      const userIds = [
        ...new Set(
          firebaseChats
            .map(
              (firebaseChat) =>
                firebaseChat.ids.filter((id) => id !== currentUserId)[0],
            )
            .filter(Boolean),
        ),
      ];

      store.dispatch(setUserIds(userIds));
    }, reportFirebaseError);
  } catch (error) {
    reportFirebaseError(error);
  }
}

export function fetchMessages(chatId, userId) {
  if (!chatId) return null;
  try {
    const database = requireDatabase();
    const q = query(collection(database, 'chat', chatId, 'message'), orderBy('time'));

    return onSnapshot(q, async (querySnapshot) => {
      const fetchedMessages = [];
      const batch = writeBatch(database);
      querySnapshot.forEach((doc) => {
        const messageRef = doc.ref;
        const message = doc.data();
        fetchedMessages.push({
          id: doc.id,
          message: message.message,
          time: message.time,
          read: message.read,
          senderId: message.senderId,
          type: message.type,
          replyDocId: message.replyDocId,
          isLast: false,
        });

        if (message.senderId !== userId && !message.read) {
          batch.update(messageRef, {
            read: true,
          });
        }
      });
      fetchedMessages.sort((a, b) => new Date(a.time) - new Date(b.time));
      if (fetchedMessages[querySnapshot.size - 1]) {
        fetchedMessages[querySnapshot.size - 1].isLast = true;
      }
      reduxBatch(() => {
        store.dispatch(setMessagesLoading(false));
        store.dispatch(setMessages(fetchedMessages));
      });
      await batch.commit().catch(reportFirebaseError);
    }, reportFirebaseError);
  } catch (error) {
    reportFirebaseError(error);
  }
}

export async function sendMessage(currentUserId, chatId, payload) {
  if (!chatId || !currentUserId) return null;
  try {
    const database = requireDatabase();
    const chatRef = doc(database, 'chat', chatId);

    await updateDoc(chatRef, {
      lastMessage: payload.message,
      time: serverTimestamp(),
    });

    const body = {
      read: false,
      time: new Date().toISOString(),
      senderId: currentUserId,
      ...payload,
    };

    if (payload.type) {
      body.type = payload.type;
    }

    await addDoc(collection(database, 'chat', chatId, 'message'), body);
  } catch (error) {
    reportFirebaseError(error);
  }
}

export async function editMessage(
  currentUserId,
  chatId,
  payload,
  editingMessage,
) {
  if (!chatId || !currentUserId || !editingMessage || !payload) return null;
  try {
    const database = requireDatabase();
    const messageRef = doc(
      database,
      'chat',
      chatId,
      'message',
      editingMessage.message.id,
    );
    if (editingMessage.message.isLast) {
      await updateDoc(doc(database, 'chat', chatId), {
        lastMessage: payload.message,
        time: serverTimestamp(),
      });
    }
    await updateDoc(messageRef, {
      message: payload.message,
    });
  } catch (error) {
    reportFirebaseError(error);
  }
}

export async function deleteChat(currentChatId) {
  try {
    await deleteDoc(doc(requireDatabase(), 'chat', currentChatId));
  } catch (error) {
    reportFirebaseError(error);
  }
}

export async function deleteMessage(chatId, message, messageBeforeLastMessage) {
  if (!chatId || !message) return null;
  try {
    const database = requireDatabase();
    await deleteDoc(doc(database, 'chat', chatId, 'message', message.id));
    if (message.isLast) {
      await updateDoc(doc(database, 'chat', chatId), {
        lastMessage: messageBeforeLastMessage
          ? messageBeforeLastMessage.message
          : '',
        time: serverTimestamp(),
      });
    }
  } catch (error) {
    reportFirebaseError(error);
  }
}

export async function fetchRepliedMessage(
  messageId,
  currentChatId,
  setReplyMessage,
) {
  if (currentChatId) {
    const q = doc(requireDatabase(), 'chat', currentChatId, 'message', messageId);
    return onSnapshot(q, (snapshot) => {
      const message = snapshot.data();
      setReplyMessage({
        id: snapshot.id,
        message: message?.message,
        type: message?.type,
      });
    }, reportFirebaseError);
  }
}

export const requestForToken = () => {
  if (!messaging) return Promise.resolve(null);
  if (!VAPID_KEY) {
    return Promise.reject(
      new Error('Firebase push notifications need VITE_FIREBASE_VAPID_KEY.'),
    );
  }

  return getToken(messaging, { vapidKey: VAPID_KEY })
    .then(async (currentToken) => {
      if (!currentToken) return null;
      store.dispatch(setFirebaseToken(currentToken));
      await userService.profileFirebaseToken({
        firebase_token: currentToken,
      });
      return currentToken;
    });
};

export const onMessageListener = (listener) => {
  if (!messaging) return () => {};
  if (typeof listener !== 'function') {
    throw new TypeError('A Firebase message listener callback is required.');
  }
  return onMessage(messaging, listener);
};
