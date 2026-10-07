import request from "../request";

const masterShopLocationService = {
    getAll: (params) => request.get('dashboard/master/shop-locations', {params}),
}

export default masterShopLocationService;
