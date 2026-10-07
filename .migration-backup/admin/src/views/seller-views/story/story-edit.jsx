import { useParams } from 'react-router-dom';
import storeisService from 'services/seller/storeis';
import StoryForm from './components/form';

const StoreisEdit = () => {
  const { id } = useParams();

  const handleSubmit = (body) => storeisService.update(id, body);

  return <StoryForm id={id} onSubmit={handleSubmit} />;
};

export default StoreisEdit;
