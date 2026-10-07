import "./_group.css";
import React, { useState } from "react";
import { CustomerAuthLayout, type AuthMode } from "./_shared/CustomerAuth";
import { CustomerLoginFlow } from "./_shared/CustomerLoginFlow";
import { CustomerRegisterFlow } from "./_shared/CustomerRegisterFlow";

export function CurrentCustomerRegister() {
  const [mode, setMode] = useState<AuthMode>("sign-up");
  const [businessMessage, setBusinessMessage] = useState("");
  return (
    <CustomerAuthLayout
      mode={mode}
      onModeChange={setMode}
      onBusiness={() => setBusinessMessage("Business login is shown in the Business Login preview.")}
    >
      {mode === "sign-up" ? <CustomerRegisterFlow /> : <CustomerLoginFlow />}
      {businessMessage && <p role="status" className="mt-4 text-sm text-gray-field">{businessMessage}</p>}
    </CustomerAuthLayout>
  );
}

export default CurrentCustomerRegister;