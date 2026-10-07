import React, { useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { useUploadImage } from "@/hook/use-upload-image";
import { error } from "@/components/alert";

type Children = (props: {
  handleClick: () => void;
  isLoading: boolean;
  handleDelete: (item: string) => void;
  preview?: string[];
}) => React.ReactNode;

interface FileUploadProps {
  fileList: string[];
  children: Children;
  onChange: (file: string[]) => void;
  acceptFiles?: string;
  type?: string;
}

export const FileUpload = ({
  fileList,
  children,
  onChange,
  acceptFiles = "application/pdf",
  type = "shop-documents",
}: FileUploadProps) => {
  const { t } = useTranslation();

  const inputRef = useRef<HTMLInputElement>(null);
  const [preview, setPreview] = useState(fileList);
  const { mutate, isLoading } = useUploadImage();

  const handleClick = () => inputRef.current?.click();

  const handleDelete = (item: string) => {
    const newPreview = preview.filter((file) => file !== item);
    setPreview(newPreview);
    onChange(newPreview);
  };

  const handleUpload: React.ChangeEventHandler<HTMLInputElement> = (e) => {
    const { files } = e.target;
    if (files?.length) {
      const filesForm = new FormData();

      [...files].forEach((file) => {
        if (file) {
          filesForm.append("image", file, file.name);
        }
      });

      filesForm.append("type", type);

      mutate(filesForm, {
        onSuccess: (res) => {
          const newPreview = [...preview, res?.data?.title];
          onChange(newPreview);
          setPreview(newPreview);
          // Reset the input value to allow re-uploading the same file
          if (inputRef.current) {
            inputRef.current.value = "";
          }
        },
        onError: () => {
          error(t("failed.to.upload"));
          // Reset the input value in case of error
          if (inputRef.current) {
            inputRef.current.value = "";
          }
        },
      });
    }
  };

  return (
    <>
      <input type="file" hidden accept={acceptFiles} ref={inputRef} onChange={handleUpload} />
      {children({ handleClick, handleDelete, isLoading, preview })}
    </>
  );
};
