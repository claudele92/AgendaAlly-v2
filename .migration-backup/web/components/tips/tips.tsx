import { useTranslation } from "react-i18next";
import { tipPercentages } from "@/config/global";
import { Price } from "@/components/price";
import { percentToPrice } from "@/utils/percent-to-price";
import Edit2FillIcon from "remixicon-react/Edit2FillIcon";
import { Button } from "@/components/button";
import React, { useState } from "react";
import clsx from "clsx";
import { Input } from "@/components/input";
import { warning } from "@/components/alert";
import { Currency } from "@/types/global";

interface TipsProps {
  totalPrice?: number;
  currency?: Currency;
  onSubmit: (tip: number) => void;
}

const Tips = ({ totalPrice = 0, currency, onSubmit }: TipsProps) => {
  const { t } = useTranslation();

  const [selectedTip, setSelectedTip] = useState<{ percent: number | string; price?: number }>({
    percent: tipPercentages[0],
    price: percentToPrice(tipPercentages[0], totalPrice),
  });

  const handleChangeCustomTip = (e: React.ChangeEvent<HTMLInputElement>) => {
    const number = Number(e.target.value);
    if (e.target.value === "") {
      setSelectedTip({ percent: "custom", price: undefined });
    } else if (number >= 0) {
      setSelectedTip({ percent: "custom", price: number });
    }
  };

  const handleSubmit = () => {
    if (selectedTip.price === undefined) {
      warning(t("please.enter.a.valid.tip"));
      return;
    }
    onSubmit(selectedTip.price);
  };

  return (
    <div className="xl:py-10 py-7 xl:px-12 md:px-6 px-4 flex flex-col gap-y-6">
      <h1 className="font-semibold text-2xl">{t("would.you.like.to.add.a.tip?")}</h1>
      <div className="grid sm:grid-cols-3 grid-cols-2 gap-3">
        {tipPercentages.map((percentage) => (
          <button
            type="button"
            key={percentage}
            className={clsx(
              "border border-footerBg rounded-xl px-5 py-3 flex flex-col items-center gap-y-1 text-lg",
              selectedTip.percent === percentage && "bg-primary border-primary text-white"
            )}
            onClick={() =>
              setSelectedTip({ percent: percentage, price: percentToPrice(percentage, totalPrice) })
            }
          >
            <span>{percentage}%</span>
            <span>
              <Price number={percentToPrice(percentage, totalPrice)} customCurrency={currency} />
            </span>
          </button>
        ))}
        <button
          type="button"
          className={clsx(
            "border border-footerBg rounded-xl px-5 py-6 flex items-center justify-center gap-x-2 text-lg",
            selectedTip.percent === "custom" && "bg-primary border-primary text-white"
          )}
          onClick={() => setSelectedTip({ percent: "custom", price: 0 })}
        >
          <span>{t("custom")}</span>
          <span>
            <Edit2FillIcon />
          </span>
        </button>
      </div>
      {selectedTip.percent === "custom" && (
        <div>
          <Input
            fullWidth
            placeholder={t("enter.custom.tip")}
            onChange={handleChangeCustomTip}
            value={selectedTip.price}
            defaultValue={selectedTip.price}
            type="number"
            pattern="[0-9]*"
          />
        </div>
      )}
      <Button type="submit" fullWidth color="black" onClick={handleSubmit}>
        {t("add")}
      </Button>
    </div>
  );
};

export default Tips;
