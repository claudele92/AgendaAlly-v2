"use client";

import React, { useEffect, useState } from "react";
import { LegalPolicyLinks } from "@/components/legal-policy-links";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { Button } from "@/components/button";
import { Empty } from "@/components/empty";
import { error, success } from "@/components/alert";
import manualFinanceService, { FinanceRequest, formatFinanceAmount } from "@/services/manual-finance";
import { clearCommandIntent, clearFinanceIntent, persistCommandIntent, persistFinanceIntent, readCommandIntent, readFinanceIntent } from "@/lib/manual-finance-intent";
import NetworkError from "@/utils/network-error";

const statusCopy: Record<string, string> = {
  REQUESTED: "Refund requested",
  APPROVED: "Refund approved — not yet refunded",
  COMPLETED: "Refund completed",
  REJECTED: "Refund rejected",
  CANCELLED: "Refund cancelled",
  REQUIRES_REVIEW: "Refund under review",
};

export default function ManualRefundsPage() {
  const queryClient = useQueryClient();
  const [sourceId, setSourceId] = useState("");
  const [method, setMethod] = useState<FinanceRequest["method"]>("bank_transfer");
  const [institution, setInstitution] = useState("");
  const [destination, setDestination] = useState("");
  const [intent, setIntent] = useState(() => readFinanceIntent("customer-refund"));
  const [hasAttempted, setHasAttempted] = useState(false);
  const capabilitiesQuery = useQuery({
    queryKey: ["manual-finance", "capabilities"],
    queryFn: () => manualFinanceService.capabilities(),
  });
  const sourcesQuery = useQuery({
    queryKey: ["manual-finance", "sources", "refund"],
    queryFn: () => manualFinanceService.sources("refund"),
    enabled: capabilitiesQuery.data?.data.can_request_refund === true,
  });
  const requestsQuery = useQuery({
    queryKey: ["manual-finance", "requests", "refund"],
    queryFn: () => manualFinanceService.requests("refund"),
  });
  const sources = sourcesQuery.data?.data || [];
  const selectedSource = sources.find((source) => String(source.allocation_id) === sourceId);

  useEffect(() => {
    if (!sourceId && sources.length) setSourceId(String(sources[0].allocation_id));
  }, [sources, sourceId]);

  const createRequest = useMutation({
    mutationFn: (body: FinanceRequest) => manualFinanceService.create(body),
    onSuccess: () => {
      clearFinanceIntent("customer-refund");
      setIntent(null);
      setHasAttempted(false);
      setDestination("");
      queryClient.invalidateQueries({ queryKey: ["manual-finance", "requests", "refund"] });
      queryClient.invalidateQueries({ queryKey: ["manual-finance", "sources", "refund"] });
      success("Refund request submitted");
    },
    onError: (err: NetworkError) => error(err.message || "The request could not be confirmed. Your saved request can be retried safely."),
  });

  const submit = () => {
    if (intent) {
      setHasAttempted(true);
      createRequest.mutate(intent.payload);
      return;
    }
    setHasAttempted(true);
    if (!selectedSource || !institution.trim() || !destination.includes("*")) return;
    const saved = persistFinanceIntent("customer-refund", {
      kind: "refund",
      allocation_id: selectedSource.allocation_id,
      ...(selectedSource.context_id ? { context_id: selectedSource.context_id } : {}),
      amount_units: selectedSource.amount_units,
      currency_code: selectedSource.currency_code,
      method,
      institution: institution.trim().toLowerCase().replace(/[^a-z0-9_-]+/g, "-"),
      destination_mask: destination.trim(),
    });
    setIntent(saved);
    setHasAttempted(true);
    createRequest.mutate(saved.payload);
  };

  const retry = () => {
    if (!intent) return;
    setHasAttempted(true);
    createRequest.mutate(intent.payload);
  };

  const cancelRequest = async (row: { id: string; version: number; state: string }) => {
    const workflow = `customer:${row.id}:cancel`;
    let intent = readCommandIntent(workflow);
    if (!intent && row.state === "APPROVED" && !window.confirm("I attest that no external refund execution occurred, no money has moved, and this request has no execution claim. Continue with cancellation?")) return;
    if (!intent) intent = persistCommandIntent(workflow, {
      version: row.version,
      reason: "Customer requested cancellation.",
      ...(row.state === "APPROVED" ? { no_execution_attested: true } : {}),
    });
    try {
      await manualFinanceService.action(row.id, "cancel", intent.payload);
      clearCommandIntent(workflow);
      queryClient.invalidateQueries({ queryKey: ["manual-finance", "requests", "refund"] });
      queryClient.invalidateQueries({ queryKey: ["manual-finance", "sources", "refund"] });
      success("Refund cancellation recorded");
    } catch (err) {
      error(err instanceof NetworkError ? err.message : "Cancellation is unresolved. The original command remains saved for a safe retry.");
    }
  };

  return (
    <section className="w-full space-y-7" data-testid="manual-refund-page">
      <header>
        <p className="text-sm text-gray-500">Account · Refunds</p>
        <h1 className="mt-1 text-2xl font-semibold">Manual refund requests</h1>
        <LegalPolicyLinks />
        <p className="mt-2 max-w-2xl text-sm text-gray-600">Choose an eligible booking or order. The amount and currency come from its recorded allocation and cannot be edited here.</p>
      </header>
      {intent && <div className="rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm" role="status">
        An earlier submission has an unresolved response. Its original request identity and details are saved for a safe retry.
        <button className="ml-2 underline" type="button" onClick={retry} disabled={createRequest.isLoading}>Retry saved request</button>
      </div>}
      {capabilitiesQuery.isLoading ? (
        <div className="h-44 animate-pulse rounded-xl bg-gray-100" aria-label="Checking refund access" />
      ) : capabilitiesQuery.isError ? (
        <div className="rounded-xl border border-red-200 p-5" role="alert">
          <p>Refund request access could not be checked.</p>
          <Button type="button" onClick={() => capabilitiesQuery.refetch()}>Try again</Button>
        </div>
      ) : capabilitiesQuery.data?.data.can_request_refund !== true ? (
        <div className="rounded-xl border border-gray-200 p-5"><p>Manual refund requests are not enabled for this account.</p></div>
      ) : sourcesQuery.isLoading ? (
        <div className="h-44 animate-pulse rounded-xl bg-gray-100" aria-label="Loading eligible refunds" />
      ) : sourcesQuery.isError ? (
        <div className="rounded-xl border border-red-200 p-5" role="alert">
          <p>Eligible refund options could not be loaded.</p>
          <Button type="button" onClick={() => sourcesQuery.refetch()}>Try again</Button>
        </div>
      ) : sources.length === 0 ? (
        <div className="rounded-xl border border-gray-200 p-5"><Empty text="No eligible manual refunds are available." smallText /></div>
      ) : (
        <div className="rounded-xl border border-gray-200 p-5 md:p-7">
          <h2 className="text-lg font-semibold">Request a refund</h2>
          <div className="mt-5 grid gap-4 md:grid-cols-2">
            <label className="grid gap-2 text-sm">Eligible booking or order
              <select className="rounded-lg border border-gray-300 bg-transparent p-3" value={sourceId} onChange={(event) => setSourceId(event.target.value)}>
                {sources.map((source) => <option key={source.allocation_id} value={source.allocation_id}>{source.source_type} · {source.source_id} · {formatFinanceAmount(source.amount_units, source.currency_code, source.money_scale)}</option>)}
              </select>
            </label>
            <div className="rounded-lg bg-gray-50 p-3 text-sm">
              <span className="block text-gray-500">Eligible amount</span>
              <strong>{selectedSource && formatFinanceAmount(selectedSource.amount_units, selectedSource.currency_code, selectedSource.money_scale)}</strong>
            </div>
            <label className="grid gap-2 text-sm">Refund method
              <select className="rounded-lg border border-gray-300 bg-transparent p-3" value={method} onChange={(event) => setMethod(event.target.value as FinanceRequest["method"])}>
                <option value="bank_transfer">Bank transfer</option><option value="mobile_money">Mobile money</option>
              </select>
            </label>
            <label className="grid gap-2 text-sm">Bank or mobile money institution
              <input className="rounded-lg border border-gray-300 bg-transparent p-3" value={institution} onChange={(event) => setInstitution(event.target.value)} placeholder="Institution name" />
            </label>
            <label className="grid gap-2 text-sm md:col-span-2">Masked destination
              <input className="rounded-lg border border-gray-300 bg-transparent p-3" value={destination} onChange={(event) => setDestination(event.target.value)} placeholder="•••• 4821" autoComplete="off" />
              <span className="text-xs text-gray-500">Enter a masked value containing * only. Do not enter account credentials or an unmasked account number.</span>
            </label>
          </div>
          {hasAttempted && (!selectedSource || !institution.trim() || !destination.includes("*")) && <p className="mt-3 text-sm text-red-700">Choose an eligible allocation, enter the institution, and provide a masked destination containing *.</p>}
          <div className="mt-5 flex flex-wrap items-center gap-3">
            <Button type="button" loading={createRequest.isLoading} onClick={submit}>Submit refund request</Button>
            <span className="text-xs text-gray-500">Submission asks the Finance team to review; it does not immediately move money.</span>
          </div>
        </div>
      )}

      <div className="rounded-xl border border-gray-200 p-5 md:p-7">
        <h2 className="text-lg font-semibold">Your refund requests</h2>
        {requestsQuery.isLoading ? <div className="mt-4 h-24 animate-pulse rounded-lg bg-gray-100" /> : requestsQuery.isError ? (
          <p className="mt-4 text-sm text-red-700">Requests could not be loaded. <button type="button" className="underline" onClick={() => requestsQuery.refetch()}>Try again</button></p>
        ) : requestsQuery.data?.data.length ? (
          <ul className="mt-4 divide-y divide-gray-100">
            {requestsQuery.data.data.map((row) => <li key={row.id} className="grid gap-2 py-4 sm:grid-cols-[1fr_auto]">
              <div><p className="font-medium" data-testid={`refund-state-${row.id}`}>{statusCopy[row.state] || "Refund status unavailable"}</p><p className="text-sm text-gray-500">Request {row.id} · {new Date(row.requested_at).toLocaleString()}</p>{row.state === "REQUIRES_REVIEW" && <p className="mt-1 text-sm font-semibold text-amber-800">Do not submit another refund while this is under review.</p>}{row.state === "COMPLETED" && row.external_reference && <p className="mt-1 text-sm">Receipt reference: {row.external_reference}</p>}</div>
              <div className="flex flex-wrap items-center gap-3"><p className="font-medium">{formatFinanceAmount(row.amount_units, row.currency_code, row.money_scale)}</p>{(row.actions?.includes("cancel") || readCommandIntent(`customer:${row.id}:cancel`)) && <button type="button" className="text-sm underline" onClick={() => cancelRequest(row)} data-testid={`cancel-refund-${row.id}`}>{readCommandIntent(`customer:${row.id}:cancel`) ? "Retry cancellation" : "Cancel request"}</button>}</div>
            </li>)}
          </ul>
        ) : <div className="mt-4"><Empty text="No manual refund requests yet." smallText /></div>}
      </div>
    </section>
  );
}
