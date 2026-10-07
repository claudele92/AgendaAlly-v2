import fs from "node:fs";
import os from "node:os";
import path from "node:path";
import crypto from "node:crypto";
import assert from "node:assert/strict";
import test from "node:test";
import { execFileSync } from "node:child_process";
import { regularFile, verifyPhoto } from "./demo-asset-review.mjs";

test("review source reads reject missing files, traversal, dotenv, directories and symlink ancestors", () => {
  const root = fs.mkdtempSync(path.join(os.tmpdir(), "demo-review-test-"));
  try {
    fs.mkdirSync(path.join(root, "public"));
    fs.writeFileSync(path.join(root, "public/photo.jpg"), "fixture");
    fs.symlinkSync(path.join(root, "public/photo.jpg"), path.join(root, "link.jpg"));
    fs.symlinkSync(path.join(root, "public"), path.join(root, "linked"));
    assert.equal(regularFile(root, "public/photo.jpg"), path.join(root, "public/photo.jpg"));
    for (const relative of ["../photo.jpg", "/photo.jpg", ".env", "public/../photo.jpg",
      "public\\photo.jpg", "public//photo.jpg", "missing.jpg", "public", "link.jpg", "linked/photo.jpg"]) {
      assert.throws(() => regularFile(root, relative));
    }
  } finally {
    fs.rmSync(root, { recursive: true });
  }
});

test("photo review rejects changed bytes, size, MIME and non-JPEG content", () => {
  const bytes = Buffer.from([0xff, 0xd8, 0xff, 0xe0, 0xff, 0xd9]);
  const asset = { bytes: bytes.length, sha256: crypto.createHash("sha256").update(bytes).digest("hex") };
  verifyPhoto(bytes, asset, "image/jpeg");
  assert.throws(() => verifyPhoto(bytes, { ...asset, bytes: 1 }, "image/jpeg"));
  assert.throws(() => verifyPhoto(bytes, { ...asset, sha256: "0".repeat(64) }, "image/jpeg"));
  assert.throws(() => verifyPhoto(bytes, asset, "text/html"));
  const html = Buffer.from("<html>");
  assert.throws(() => verifyPhoto(html, {
    bytes: html.length, sha256: crypto.createHash("sha256").update(html).digest("hex")
  }, "image/jpeg"));
});

test("execution and installation modes are rejected before source access", () => {
  for (const mode of ["--install", "--copy", "--approve", "--initialize"]) {
    assert.throws(() => execFileSync(process.execPath,
      ["scripts/database/demo-asset-review.mjs", mode], { stdio: "pipe" }),
    error => error.status === 1 && error.stderr.toString().includes("no installation mode exists"));
  }
});
