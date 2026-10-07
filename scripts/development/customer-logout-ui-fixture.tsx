// Test-only consumers of the actual native hook/store/cookie/query runtime.
import React from "react";
import { createRoot } from "react-dom/client";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { setCookie } from "cookies-next";
import { ToastContainer } from "react-toastify";
import { useAuth } from "@/hook/use-auth";
import useUserStore from "@/global-store/user";

const client = new QueryClient({defaultOptions:{queries:{retry:false},mutations:{retry:false}}});
const fixture = window as any;
fixture.nativeLogoutFixture = {
  navigationCount: 0, refreshCount: 0, logoutRequests: 0, lastPushPresent: null,
  protectedStatus: null, unrelatedStatus: null, siblingStatus: null,
};
let revokedSessionCredential = "";
let unrelatedCredential = "";
let siblingCredential = "";
const nativeFetch = window.fetch.bind(window);
window.fetch = async (input, init) => {
  if (String(input).endsWith("/api/v1/auth/logout")) {
    fixture.nativeLogoutFixture.logoutRequests += 1;
    fixture.nativeLogoutFixture.lastPushPresent = Boolean(JSON.parse(String(init?.body || "{}")).token);
  }
  return nativeFetch(input, init);
};

function Menu() {
  const {isSignedIn} = useAuth();
  const user = useUserStore(s=>s.user);
  return <nav aria-label="Customer menu">
    <span data-testid="menu-state">{isSignedIn ? "Signed in" : "Signed out"}</span>
    {user && isSignedIn ? <a href="#profile">Profile</a> : <a href="#login">Sign in</a>}
  </nav>;
}
function SessionPanel() {
  const {logOut,isSignedIn} = useAuth();
  const user = useUserStore(s=>s.user);
  const start = async () => {
    try {
    const response=await nativeFetch("/fixture/session",{method:"POST"});
    fixture.nativeLogoutFixture.sessionSetupStatus=response.status;
    if (!response.ok) throw new Error(`Synthetic session HTTP ${response.status}`);
    const data=await response.json();
    fixture.nativeLogoutFixture.sessionSetupKeys=Object.keys(data);
    if (!data.current || !data.sibling || !data.other) throw new Error("Invalid synthetic session response");
    revokedSessionCredential=data.current;
    siblingCredential=data.sibling;
    unrelatedCredential=data.other;
    setCookie("token",`Bearer ${data.current}`);
    client.setQueryData(["profile"],{private:true});
    useUserStore.getState().signIn({id:1,firstname:"Synthetic Customer"} as any);
    } catch {
      document.getElementById("access-result")!.textContent="Synthetic session setup failed (inspect sanitized HTTP metadata).";
    }
  };
  const check = async () => {
    for (const [name,credential] of [
      ["protectedStatus",revokedSessionCredential],
      ["siblingStatus",siblingCredential],["unrelatedStatus",unrelatedCredential],
    ]) {
      const res=await nativeFetch("/protected",{headers:{Authorization:`Bearer ${credential}`}});
      fixture.nativeLogoutFixture[name]=res.status;
    }
    document.getElementById("access-result")!.textContent=JSON.stringify({
      revoked:fixture.nativeLogoutFixture.protectedStatus,
      sibling:fixture.nativeLogoutFixture.siblingStatus,other:fixture.nativeLogoutFixture.unrelatedStatus,
    });
  };
  return <main>
    <h1>Isolated native Customer logout check</h1>
    <p>This uses the production logout hook with synthetic authentication only.</p>
    <p data-testid="panel-state">{user && isSignedIn ? "Authenticated panel" : "Anonymous panel"}</p>
    <button onClick={start}>Start synthetic session</button>
    <button onClick={()=>void logOut()}>Logout</button>
    <button onClick={check}>Check session access</button>
    <pre id="access-result"/>
  </main>;
}
createRoot(document.getElementById("root")!).render(
  <QueryClientProvider client={client}><Menu/><SessionPanel/><ToastContainer/></QueryClientProvider>
);
