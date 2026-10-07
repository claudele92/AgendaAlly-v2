import React from "react";

const SingleShopLoading = () => (
  <div className="aa-s2 aa-s2-profile-page animate-pulse">
    <main className="aa-s2-wrap aa-s2-profile-content">
      <div className="h-4 w-48 rounded-full bg-gray-300 mb-5" />
      <div className="aa-s2-profile-cover">
        <div className="min-h-[220px] md:min-h-[300px] bg-gray-300" />
        <div className="p-6 md:p-8">
          <div className="h-7 w-2/3 rounded-full bg-gray-300" />
          <div className="h-4 w-full max-w-lg rounded-full bg-gray-300 mt-5" />
          <div className="h-4 w-3/4 max-w-md rounded-full bg-gray-300 mt-3" />
          <div className="h-9 w-40 rounded-full bg-gray-300 mt-5" />
        </div>
      </div>
      <div className="flex gap-2 mt-5 mb-5">
        <div className="h-9 w-24 rounded-full bg-gray-300" />
        <div className="h-9 w-24 rounded-full bg-gray-300" />
        <div className="h-9 w-28 rounded-full bg-gray-300" />
      </div>
      <div className="grid lg:grid-cols-[minmax(0,1fr)_310px] gap-5">
        <div className="grid md:grid-cols-2 gap-4">
          <div className="h-[420px] rounded-button bg-gray-300" />
          <div className="h-[420px] rounded-button bg-gray-300" />
          <div className="h-[220px] md:col-span-2 rounded-button bg-gray-300" />
        </div>
        <div className="flex flex-col gap-4">
          <div className="h-48 rounded-button bg-gray-300" />
          <div className="h-48 rounded-button bg-gray-300" />
        </div>
      </div>
    </main>
  </div>
);

export default SingleShopLoading;
