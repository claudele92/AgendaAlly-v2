import { useTranslation } from "react-i18next";
import { Input } from "@/components/input";
import { Button } from "@/components/button";
import { Price } from "@/components/price";
import useUserStore from "@/global-store/user";
import { getNearestFloor } from "@/utils/get-nearest-floor";
import { useState } from "react";

const getLowest = (value1?: number, value2?: number) =>
  (value1 || 0) <= (value2 || 0) ? value1 : value2;

interface WalletFormProps {
  totalPrice?: number;
  onSave: ({
    fromWalletPrice,
    isFullPricePaid,
  }: {
    fromWalletPrice?: number;
    isFullPricePaid: boolean;
  }) => void;
  onCancel: () => void;
}

export const WalletForm = ({ totalPrice, onSave, onCancel }: WalletFormProps) => {
  const { t } = useTranslation();
  const user = useUserStore((state) => state.user);

  const lowest = getLowest(totalPrice, user?.wallet?.price);

  const [fromWalletPriceLocal, setFromWalletPriceLocal] = useState<number | undefined>(lowest);

  const walletPrice = getNearestFloor(user?.wallet?.price);

  const remaining = Math.max(walletPrice - (fromWalletPriceLocal || 0), 0);

  const handleChange = (value: number | undefined) => {
    if ((value || 0) > (lowest || 0)) {
      return;
    }
    setFromWalletPriceLocal(value);
  };

  const handleSave = () => {
    onSave({
      fromWalletPrice: fromWalletPriceLocal,
      isFullPricePaid: fromWalletPriceLocal === totalPrice,
    });
  };

  return (
    <div className="px-4 py-5">
      <h1 className="font-medium text-xl text-center mb-1">
        {t("you.can.pay.the.full.amount.with.your.wallet")}
      </h1>
      <p className="text-center mb-6">{t("want.to.pay.via.your.wallet?")}</p>
      <Input
        type="number"
        label="amount"
        fullWidth
        value={fromWalletPriceLocal}
        min={0}
        max={lowest}
        onKeyPress={(e) => {
          // Allow only digits and special keys like backspace, delete, arrows
          const isNumber = /[0-9]|\./;
          if (
            !isNumber.test(e.key) &&
            e.key !== "Backspace" &&
            e.key !== "Delete" &&
            e.key !== "ArrowLeft" &&
            e.key !== "ArrowRight"
          ) {
            e.preventDefault();
          }
        }}
        onChange={(e) => {
          const value = e.target.value ? +e.target.value : undefined;
          handleChange(value);
        }}
      />
      <div className="flex items-center justify-center gap-x-1 mt-6">
        <p className="font-medium text-base">{t("remaining.wallet.balance")}:</p>
        <p className="font-medium text-base">
          <Price number={remaining} />
        </p>
      </div>
      <div className="flex gap-x-2.5 mt-5">
        <Button size="small" color="blackOutlined" fullWidth onClick={onCancel}>
          {t("cancel")}
        </Button>
        <Button size="small" fullWidth onClick={handleSave}>
          {t("save")}
        </Button>
      </div>
    </div>
  );
};
