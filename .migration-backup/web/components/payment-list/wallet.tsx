import Image from "next/image";
import useUserStore from "@/global-store/user";
import { Price } from "@/components/price";
import { useTranslation } from "react-i18next";
import { useModal } from "@/hook/use-modal";
import { Modal } from "@/components/modal";
import { LoadingCard } from "@/components/loading";
import dynamic from "next/dynamic";

const WalletForm = dynamic(
  () => import("./wallet-form").then((component) => ({ default: component.WalletForm })),
  {
    loading: () => <LoadingCard />,
  }
);

interface WalletProps {
  fromWalletPrice?: number;
  totalPrice?: number;
  onFullPricePaid: () => void;
  onChangeWalletPrice: (value?: number) => void;
}

export const Wallet = ({
  fromWalletPrice,
  totalPrice,
  onFullPricePaid,
  onChangeWalletPrice,
}: WalletProps) => {
  const { t } = useTranslation();
  const user = useUserStore((state) => state.user);
  const [isOpen, handleOpen, handleClose] = useModal();
  const handleSave = ({
    fromWalletPrice: walletPrice,
    isFullPricePaid,
  }: {
    isFullPricePaid: boolean;
    fromWalletPrice?: number;
  }) => {
    handleClose();
    if (isFullPricePaid) {
      onFullPricePaid();
    }
    onChangeWalletPrice(walletPrice);
  };
  const handleRemove = () => {
    onChangeWalletPrice();
  };
  return (
    <>
      <div className="border-2 border-primary rounded-xl p-6 ">
        <div className="flex items-center gap-x-4 mb-2.5">
          <Image src="/img/purse.png" alt="purse" width={24} height={24} />
          <span className="font-semibold text-xl !leading-6">
            <Price number={fromWalletPrice || user?.wallet?.price} />
          </span>
        </div>
        <p className="font-normal text-sm mb-4">
          {t(fromWalletPrice ? "has.paid.by.wallet" : "balance.in.your.wallet")}
        </p>
        <div className="flex justify-between items-center">
          <p>{t(fromWalletPrice ? "applied" : "do.you.want.to.use.now")}</p>
          {fromWalletPrice ? (
            <button
              type="button"
              className="bg-[#D21234] font-medium text-base !leading-5 px-4 py-2.5 rounded-lg text-white cursor-pointer active:translate-y-1 transition-all duration-200"
              onClick={handleRemove}
            >
              {t("remove")}
            </button>
          ) : (
            <button
              type="button"
              className="bg-primary font-medium text-base !leading-5 px-4 py-2.5 rounded-lg text-white cursor-pointer active:translate-y-1 transition-all duration-200"
              onClick={handleOpen}
            >
              {t("use")}
            </button>
          )}
        </div>
      </div>
      <Modal isOpen={isOpen} onClose={handleClose} size="small">
        <WalletForm totalPrice={totalPrice} onSave={handleSave} onCancel={handleClose} />
      </Modal>
    </>
  );
};
