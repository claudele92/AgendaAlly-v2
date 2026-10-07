import "./_group.css";
import React, { useState } from "react";
import { CustomerAuthLayout, type AuthMode } from "./_shared/CustomerAuth";
import { CustomerLoginFlow } from "./_shared/CustomerLoginFlow";
import { CustomerRegisterFlow } from "./_shared/CustomerRegisterFlow";

export function CurrentCustomerLogin() {
  const [mode, setMode] = useState<AuthMode>("login");
  const [businessMessage, setBusinessMessage] = useState("");
  return (
    <CustomerAuthLayout
      mode={mode}
      onModeChange={setMode}
      onBusiness={() => setBusinessMessage("Business login is shown in the Business Login preview.")}
    >
      {mode === "login" ? <CustomerLoginFlow /> : <CustomerRegisterFlow />}
      {businessMessage && <p role="status" className="mt-4 text-sm text-gray-field">{businessMessage}</p>}
    </CustomerAuthLayout>
  );
}

export default CurrentCustomerLogin;