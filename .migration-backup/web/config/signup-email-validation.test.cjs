"use strict";

const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const path = require("node:path");
const test = require("node:test");

const source = (relativePath) =>
  readFileSync(path.resolve(__dirname, "..", relativePath), "utf8");

const signUpForm = source(
  "components/auth/sign-up/components/sign-up-form/sign-up-form.tsx",
);
const sharedInput = source("components/input/input.tsx");

test("sign-up email uses native validation before the credential submit handler", () => {
  assert.match(
    signUpForm,
    /<Input\s+\{\.\.\.register\("email"\)\}\s+type="email"/,
  );
  assert.match(sharedInput, /type=\{inputType\}/);
  assert.match(sharedInput, /useState\(type \|\| "text"\)/);
  assert.match(
    signUpForm,
    /<form id="signUp" onSubmit=\{handleSubmit\(handleCheckCredential\)\}>/,
  );
  assert.doesNotMatch(signUpForm, /<form[^>]*noValidate/);
  assert.match(
    signUpForm,
    /email: currentType === "email" \? yup\.string\(\)\.email\(\)\.required\(\)/,
  );
  assert.match(signUpForm, /\.signUp\(\{ email: data\.email as string \}\)/);
  assert.match(signUpForm, /form="signUp"[\s\S]*?type="submit"/);
});

test("sign-up terms gate and phone contact flow remain unchanged", () => {
  assert.match(signUpForm, /disabled=\{!watch\("agreed"\)\}/);
  assert.match(signUpForm, /currentType === "phone" \? \(\s*<PhoneInput/);
  assert.match(signUpForm, /phoneNumberSignIn\(phoneNumber as string\)/);
});
