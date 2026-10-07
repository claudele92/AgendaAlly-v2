"use client";

import { Translate } from "@/components/translate";
import AnimatedContent from "@/components/animated-content";
import * as animationData from "@/public/lottie/payment_failed.json";

const PaymentError = () => (
  <section className="h-[70vh] flex flex-col items-center justify-center">
    <div className="sm:w-[500px] w-[350px]">
      <AnimatedContent animationData={animationData} />
    </div>
    <h1 className="text-center font-semibold sm:text-xl text-lg text-red-600">
      <Translate value="payment.error" />
    </h1>
  </section>
);

export default PaymentError;
