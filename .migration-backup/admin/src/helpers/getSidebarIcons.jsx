import {
  LuCalendarCog,
  LuCalendarDays,
  LuFileBox,
  LuLayoutDashboard,
} from 'react-icons/lu';
import {
  FaBusinessTime,
  FaCashRegister,
  FaListOl,
  FaPuzzlePiece,
  FaWarehouse,
  FaWpforms,
} from 'react-icons/fa';
import {
  AppstoreAddOutlined,
  BarChartOutlined,
  BookOutlined,
  BoxPlotOutlined,
  BranchesOutlined,
  CalendarOutlined,
  CarOutlined,
  CheckCircleOutlined,
  CheckOutlined,
  CloudUploadOutlined,
  CommentOutlined,
  CopyrightOutlined,
  DatabaseOutlined,
  DisconnectOutlined,
  DollarOutlined,
  DropboxOutlined,
  EuroCircleOutlined,
  FileTextOutlined,
  FireOutlined,
  FormOutlined,
  FundViewOutlined,
  GiftOutlined,
  GlobalOutlined,
  GoldOutlined,
  GroupOutlined,
  IdcardOutlined,
  InstagramOutlined,
  LaptopOutlined,
  LockOutlined,
  LogoutOutlined,
  MailOutlined,
  MessageOutlined,
  MoneyCollectOutlined,
  OrderedListOutlined,
  PaperClipOutlined,
  PieChartOutlined,
  PlusOutlined,
  ProjectOutlined,
  QrcodeOutlined,
  QuestionCircleOutlined,
  RadarChartOutlined,
  RiseOutlined,
  ScissorOutlined,
  SettingOutlined,
  ShopOutlined,
  ShoppingCartOutlined,
  SkinOutlined,
  SlidersOutlined,
  SnippetsOutlined,
  StarOutlined,
  StockOutlined,
  ThunderboltOutlined,
  ToolOutlined,
  TransactionOutlined,
  TranslationOutlined,
  TrophyOutlined,
  UngroupOutlined,
  UserAddOutlined,
  UsergroupAddOutlined,
  UserOutlined,
  UserSwitchOutlined,
  WalletOutlined,
} from '@ant-design/icons';
import {
  PiDotOutlineFill,
  PiHandCoins,
  PiWarningCircleLight,
} from 'react-icons/pi';
import {
  MdCurrencyExchange,
  MdDeliveryDining,
  MdEditNotifications,
  MdManageHistory,
  MdNotificationAdd,
  MdOutlineEmail,
  MdOutlineFastfood,
  MdOutlineNotificationsActive,
  MdOutlinePayment,
  MdOutlinePayments,
  MdWorkOutline,
} from 'react-icons/md';
import { BiCategoryAlt, BiMapPin, BiMoneyWithdraw } from 'react-icons/bi';
import {
  BsBarChart,
  BsBoxSeam,
  BsCalendarCheck,
  BsClockHistory,
  BsFillDiagram3Fill,
  BsHouse,
  BsHouses,
  BsImage,
  BsInfoCircle,
  BsLightningCharge,
} from 'react-icons/bs';
import {
  HiOutlineChartBar,
  HiOutlineClipboardList,
  HiOutlineDocumentReport,
  HiOutlinePuzzle,
  HiOutlineReceiptRefund,
} from 'react-icons/hi';
import { FiClock, FiImage } from 'react-icons/fi';
import { ImStatsDots, ImSubscript2 } from 'react-icons/im';
import {
  AiOutlineBell,
  AiOutlineClear,
  AiOutlineFieldTime,
  AiOutlineStock,
} from 'react-icons/ai';
import {
  RiCoupon2Line,
  RiEBike2Line,
  RiFileSettingsLine,
  RiGalleryFill,
  RiListSettingsLine,
  RiPagesLine,
  RiRoadMapLine,
} from 'react-icons/ri';
import {
  TbAlignBoxBottomCenter,
  TbBasketStar,
  TbBrandAirtable,
  TbDatabaseCog,
  TbDeviceMobileCog,
  TbGiftFilled,
  TbMessagePlus,
  TbReportAnalytics,
  TbRulerMeasure,
  TbSitemap,
  TbTriangleSquareCircle,
  TbTruckDelivery,
  TbUsersGroup,
} from 'react-icons/tb';
import { GiPayMoney } from 'react-icons/gi';
import { GrAppleAppStore, GrServicePlay } from 'react-icons/gr';
import { CiDiscount1, CiWallet } from 'react-icons/ci';
import { IoImagesOutline } from 'react-icons/io5';
import {
  FaGears,
  FaKitchenSet,
  FaMoneyBillTransfer,
  FaMoneyBillTrendUp,
  FaUsersGear,
} from 'react-icons/fa6';
import {
  LiaAdSolid,
  LiaLanguageSolid,
  LiaMoneyCheckSolid,
} from 'react-icons/lia';
import { IoMdSettings } from 'react-icons/io';
import { HiOutlineClipboardDocumentList } from 'react-icons/hi2';
import { RxDotFilled } from 'react-icons/rx';

export const getSidebarIcons = (icon, size) => {
  switch (icon) {
    case 'pos':
      return <FaCashRegister size={size} />;
    case 'order_statuses':
      return <CheckCircleOutlined size={size} />;
    case 'order_reviews':
      return <CommentOutlined size={size} />;
    case 'deliveryzone':
      return <ShopOutlined />;
    case 'dashboard':
      return <PieChartOutlined />;
    case 'shop':
      return <ShopOutlined />;
    case 'gold':
      return <GoldOutlined />;
    case 'dropbox':
      return <MdOutlineFastfood />;
    case 'appStoreAdd':
      return <AppstoreAddOutlined />;
    case 'laptop':
      return <LaptopOutlined />;
    case 'appStore':
      return <BiCategoryAlt />;
    case 'settings':
      return <SettingOutlined />;
    case 'global':
      return <GlobalOutlined />;
    case 'moneyCollect':
      return <MoneyCollectOutlined />;
    case 'branches':
      return <BranchesOutlined />;
    case 'branchesOutlined':
      return <BranchesOutlined />;
    case 'user':
      return <UserOutlined />;
    case 'userSwitch':
      return <UserSwitchOutlined />;
    case 'userAdd':
      return <UserAddOutlined />;
    case 'calendar':
      return <CalendarOutlined />;
    case 'BsCalendarCheck':
      return <BsCalendarCheck />;
    case 'euroCircle':
      return <EuroCircleOutlined />;
    case 'translation':
      return <TranslationOutlined />;
    case 'project':
      return <ProjectOutlined />;
    case 'database':
      return <DatabaseOutlined />;
    case 'tool':
      return <ToolOutlined />;
    case 'disconnect':
      return <DisconnectOutlined />;
    case 'orderedList':
      return <OrderedListOutlined />;
    case 'form':
      return <FormOutlined />;
    case 'wallet':
      return <WalletOutlined />;
    case 'userGroupAdd':
      return <UsergroupAddOutlined />;
    case 'questionCircle':
      return <QuestionCircleOutlined />;
    case 'transaction':
      return <TransactionOutlined />;
    case 'fiShoppingCart':
      return <ShoppingCartOutlined />;
    case 'OrderedListOutlined':
      return <OrderedListOutlined />;
    case 'CiViewBoard':
      return <HiOutlineClipboardList />;
    case 'fiImage':
      return <FiImage />;
    case 'bsImage':
      return <BsImage />;
    case 'deliveryDining':
      return <MdDeliveryDining />;
    case 'thunderbolt':
      return <ThunderboltOutlined />;
    case 'notificationsActive':
      return <MdOutlineNotificationsActive />;
    case 'imSubscript':
      return <ImSubscript2 />;
    case 'caretUp':
      return <BsBarChart />;
    case 'info':
      return <BsInfoCircle />;
    case 'RiPageSeparator':
      return <SnippetsOutlined />;
    case 'AiOutlineFieldTime':
      return <AiOutlineFieldTime />;
    case 'CarOutlined':
      return <CarOutlined />;
    case 'message':
      return <MessageOutlined />;
    case 'lock':
      return <LockOutlined />;
    case 'paperClip':
      return <PaperClipOutlined />;
    case 'star':
      return <StarOutlined />;
    case 'skin':
      return <SkinOutlined />;
    case 'book':
      return <BookOutlined />;
    case 'cloudUpload':
      return <CloudUploadOutlined />;
    case 'fire':
      return <FireOutlined />;
    case 'dollar':
      return <DollarOutlined />;
    case 'trophy':
      return <TrophyOutlined />;
    case 'instagram':
      return <InstagramOutlined />;
    case 'copyright':
      return <CopyrightOutlined />;
    case 'logout':
      return <LogoutOutlined />;
    case 'BsClockHistory':
      return <BsClockHistory />;
    case 'RiFileSettingsLine':
      return <RiFileSettingsLine />;
    case 'GiftOutlined':
      return <GiftOutlined />;
    case 'MdNotificationAdd':
      return <MdNotificationAdd />;
    case 'emailSettings':
      return <MdOutlineEmail />;
    case 'TbReceiptRefund':
      return <HiOutlineReceiptRefund />;
    case 'report':
      return <BarChartOutlined />;
    case 'revenue':
      return <RiseOutlined />;
    case 'orders':
      return <RadarChartOutlined />;
    case 'variation':
      return <BoxPlotOutlined />;
    case 'CaretUpOutlined':
      return <HiOutlineChartBar style={{ marginRight: '13px' }} />;
    case 'careerCategory':
      return <BiCategoryAlt />;
    case 'categories':
      return <SlidersOutlined />;
    case 'stock':
      return <StockOutlined />;
    case 'TbTruckDelivery':
      return <TbTruckDelivery />;
    case 'TbSitemap':
      return <TbSitemap />;
    case 'MdOutlineTableBar':
      return <TbBrandAirtable />;
    case 'AiOutlineClear':
      return <AiOutlineClear />;
    case 'SlPuzzle':
      return <HiOutlinePuzzle />;
    case 'ImStatsDots':
      return <ImStatsDots />;
    case 'extras':
      return <PlusOutlined />;
    case 'mail':
      return <MailOutlined />;
    case 'moneyOut':
      return <GiPayMoney />;
    case 'groupOutlined':
      return <GroupOutlined />;
    case 'unGroupOutlined':
      return <UngroupOutlined />;
    case 'GrAppleAppStore':
      return <GrAppleAppStore />;
    case 'lightning':
      return <BsLightningCharge />;
    case 'clock':
      return <FiClock />;
    case 'payload':
      return <MdOutlinePayment />;
    case 'recept':
      return <TbTriangleSquareCircle />;
    case 'BookOutlined':
      return <BookOutlined />;
    case 'BiMapPin':
      return <BiMapPin />;
    case 'FileTextOutlined':
      return <FileTextOutlined />;
    case 'check':
      return <CheckOutlined />;
    case 'QrcodeOutlined':
      return <QrcodeOutlined />;
    case 'delivery':
      return <RiRoadMapLine />;
    case 'warehouse':
      return <FaWarehouse />;
    case 'looks':
      return <StarOutlined />;
    case 'QuestionCircleOutlined':
      return <QuestionCircleOutlined />;
    case 'service':
      return <ScissorOutlined />;
    case 'scissors':
      return <ScissorOutlined />;
    case 'invitations':
      return <FaListOl />;
    case 'businessTime':
      return <FaBusinessTime />;
    case 'gift':
      return <GiftOutlined />;
    case 'bell':
      return <AiOutlineBell />;
    case 'forms':
      return <FaWpforms />;
    case 'idCart':
      return <IdcardOutlined />;
    case 'brands':
      return <TbBasketStar size={size} />;
    case 'units':
      return <TbRulerMeasure size={size} />;
    case 'products':
      return <BsBoxSeam size={size} />;
    case 'addons':
      return <FaPuzzlePiece size={size} />;
    case 'options':
      return <PiHandCoins size={size} />;
    case 'discounts':
      return <CiDiscount1 size={size} />;
    case 'main_branch':
      return <BsHouse size={size} />;
    case 'branch_list':
      return <BsHouses size={size} />;
    case 'gallery':
      return <IoImagesOutline size={size} />;
    case 'kitchen_list':
      return <FaKitchenSet size={size} />;
    case 'reservation_set_up':
      return <LuCalendarCog size={size} />;
    case 'reservations':
      return <LuCalendarDays size={size} />;
    case 'stories':
      return <MdManageHistory size={size} />;
    case 'blogs':
      return <TbMessagePlus size={size} />;
    case 'careers':
      return <MdWorkOutline size={size} />;
    case 'banners':
      return <LiaAdSolid size={size} />;
    case 'notifications':
      return <MdOutlineNotificationsActive size={size} />;
    case 'coupons':
      return <RiCoupon2Line size={size} />;
    case 'referral':
      return <BsFillDiagram3Fill size={size} />;
    case 'bonuses':
      return <TbGiftFilled size={size} />;
    case 'customers':
      return <TbUsersGroup size={size} />;
    case 'staff_and_admin_users':
      return <FaUsersGear size={size} />;
    case 'deliverymen':
      return <RiEBike2Line size={size} />;
    case 'wallets':
      return <CiWallet size={size} />;
    case 'transactions':
      return <FaMoneyBillTransfer size={size} />;
    case 'seller_payments':
      return <LiaMoneyCheckSolid size={size} />;
    case 'deliveryman_payments':
      return <GiPayMoney size={size} />;
    case 'payout_requests':
      return <FaMoneyBillTrendUp size={size} />;
    case 'payouts':
      return <BiMoneyWithdraw size={size} />;
    case 'general_settings':
      return <IoMdSettings size={size} />;
    case 'currencies':
      return <MdCurrencyExchange size={size} />;
    case 'payments':
      return <MdOutlinePayments size={size} />;
    case 'notification_settings':
      return <MdEditNotifications size={size} />;
    case 'social_settings':
      return <GrServicePlay size={size} />;
    case 'app_settings':
      return <TbDeviceMobileCog size={size} />;
    case 'page_setup':
      return <RiPagesLine size={size} />;
    case 'languages_and_translations':
      return <LiaLanguageSolid size={size} />;
    case 'backup_and_maintenance':
      return <FaGears size={size} />;
    case 'system_information':
      return <PiWarningCircleLight size={size} />;
    case 'update_database':
      return <TbDatabaseCog size={size} />;
    case 'overview':
      return <HiOutlineDocumentReport size={size} />;
    case 'revenue_reports':
      return <TbReportAnalytics size={size} />;
    case 'order_reports':
      return <TbAlignBoxBottomCenter size={size} />;
    case 'product_reports':
      return <LuFileBox size={size} />;
    case 'stock_reports':
      return <AiOutlineStock size={size} />;
    case 'category_reports':
      return <RiListSettingsLine size={size} />;
    case 'options_extras_reports':
      return <HiOutlineClipboardDocumentList size={size} />;
    default:
      return <RxDotFilled size={size} />;
  }
};
