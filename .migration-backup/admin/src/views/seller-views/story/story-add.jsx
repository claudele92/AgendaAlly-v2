import storeisService from 'services/seller/storeis';
import StoryForm from './components/form';

const StoresAdd = () => {
  const handleSubmit = (body) => storeisService.create(body);

  return <StoryForm onSubmit={handleSubmit} />;
};

export default StoresAdd;
