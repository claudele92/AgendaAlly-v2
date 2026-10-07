"use client";

import { useEffect, useState } from "react";
import { getFirebaseApp, getFirebaseConfiguration } from "@/lib/firebase";
import { deleteCookie, hasCookie } from "cookies-next";
import useUserStore from "@/global-store/user";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import useSearchHistoryStore from "@/global-store/search-history";
import useLikeStore from "@/global-store/like";
import useCartStore from "@/global-store/cart";
import useCompareStore from "@/global-store/compare";
import { useFcmToken } from "@/hook/use-fcm-token";
import { authService } from "@/services/auth";
import { useRouter } from "next/navigation";
import { toast } from "react-toastify";

export const useAuth = () => {
  const router = useRouter();
  const queryClient = useQueryClient();
  const [isSignedIn, setIsSignedIn] = useState(false);
  const user = useUserStore((state) => state.user);
  const { signOut: localSignOut } = useUserStore();
  const clearSearchHistory = useSearchHistoryStore((state) => state.clear);
  const clearLikeList = useLikeStore((state) => state.clear);
  const clearCart = useCartStore((state) => state.clear);
  const clearCompareList = useCompareStore((state) => state.clear);
  const { fcmToken } = useFcmToken();
  const { mutateAsync: serverLogout } = useMutation({
    mutationFn: (body: { token?: string }) => authService.logout(body),
    mutationKey: ["logout"],
  });
  const handleLogoutLocal = async () => {
    // Publish the auth change before optional Firebase cleanup or navigation.
    setIsSignedIn(false);
    deleteCookie("token");
    localSignOut();
    await queryClient.cancelQueries();
    queryClient.clear();
    clearSearchHistory();
    clearLikeList("product");
    clearCart();
    clearCompareList();
    if (getFirebaseConfiguration()) {
      // Optional provider cleanup must not delay the logged-out UI.
      void import("firebase/auth")
        .then(({ signOut, getAuth }) => signOut(getAuth(getFirebaseApp())))
        .catch(() => undefined);
    }
  };
  const logOut = async () => {
    try {
      // Keep the auth cookie until the server has revoked this session.
      // Push registration is optional metadata, never an auth prerequisite.
      await serverLogout(fcmToken ? { token: fcmToken } : {});
    } catch {
      toast.error("Logout could not be confirmed. Please try again.");
      return false;
    }
    await handleLogoutLocal();
    router.replace("/");
    router.refresh();
    return true;
  };

  const googleSignIn = async () => {
    const { signInWithPopup, GoogleAuthProvider, getAuth } = await import("firebase/auth");
    const auth = getAuth(getFirebaseApp());
    const googleAuthProvider = new GoogleAuthProvider();
    return signInWithPopup(auth, googleAuthProvider);
  };

  const appleSignIn = async () => {
    const { signInWithPopup, OAuthProvider, getAuth } = await import("firebase/auth");
    const auth = getAuth(getFirebaseApp());
    const appleAuthProvider = new OAuthProvider("apple.com");
    appleAuthProvider.addScope("email");
    appleAuthProvider.addScope("name");
    return signInWithPopup(auth, appleAuthProvider);
  };

  const facebookSignIn = async () => {
    const { FacebookAuthProvider, signInWithPopup, getAuth } = await import("firebase/auth");
    const auth = getAuth(getFirebaseApp());
    const facebookAuthProvider = new FacebookAuthProvider();
    return signInWithPopup(auth, facebookAuthProvider);
  };

  const phoneNumberSignIn = async (phoneNumber: string) => {
    const { getAuth, signInWithPhoneNumber, RecaptchaVerifier } = await import("firebase/auth");
    const auth = getAuth(getFirebaseApp());
    const appVerifier = new RecaptchaVerifier(auth, "sign-in-button", {
      size: "invisible",
      callback: () => {
        console.log("Callback!");
      },
    });
    return signInWithPhoneNumber(auth, phoneNumber, appVerifier);
  };

  useEffect(() => {
    setIsSignedIn(hasCookie("token"));
  }, [user]);

  return { logOut, googleSignIn, appleSignIn, facebookSignIn, phoneNumberSignIn, isSignedIn };
};
