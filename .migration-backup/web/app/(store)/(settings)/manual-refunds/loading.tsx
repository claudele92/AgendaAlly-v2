export default function ManualRefundsLoading() {
  return (
    <div className="w-full space-y-6" aria-label="Loading manual refunds">
      <div className="h-9 w-64 animate-pulse rounded bg-gray-100" />
      <div className="h-52 animate-pulse rounded-2xl bg-gray-100" />
      <div className="h-40 animate-pulse rounded-2xl bg-gray-100" />
    </div>
  );
}
