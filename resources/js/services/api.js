import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Products
export const getProducts = (params = {}) => api.get('/products', { params });
export const getProductBySlug = (slug) => api.get(`/products/${slug}`);
export const getFeaturedProducts = () => api.get('/products/featured');
export const getNewProducts = () => api.get('/products/new');
export const getHotSaleProducts = () => api.get('/products/hot-sale');
export const getRelatedProducts = (productId) => api.get(`/products/${productId}/related`);
export const getProductsByCategory = (slug) => api.get(`/categories/${slug}/products`);
export const getProductsByBrand = (slug) => api.get(`/brands/${slug}/products`);

// Categories
export const getCategories = () => api.get('/categories');
export const getCategoryBySlug = (slug) => api.get(`/categories/${slug}`);

// Brands
export const getBrands = () => api.get('/brands');

// News
export const getNews = (params = {}) => api.get('/news', { params });
export const getLatestNews = () => api.get('/news/latest');
export const getNewsBySlug = (slug) => api.get(`/news/${slug}`);

// Banners
export const getActiveBanners = () => api.get('/banners/active');
export const getAllBanners = () => api.get('/banners');

// Campaigns
export const getActiveCampaigns = () => api.get('/campaigns/active');

// Reviews
export const getProductReviews = (productId) => api.get(`/products/${productId}/reviews`);
export const submitReview = (data) => api.post('/reviews', data);

// Orders (cần authentication)
export const createOrder = (data) => api.post('/orders', data);
export const getMyOrders = () => api.get('/orders/my-orders');
export const getOrderDetail = (orderId) => api.get(`/orders/${orderId}`);

// Quote Requests
export const submitQuoteRequest = (data) => api.post('/quote-requests', data);
export const getMyQuoteRequests = () => api.get('/quote-requests/my-requests');

// Cart (sử dụng localStorage hoặc API nếu có)
export const addToCart = (variantId, quantity) => api.post('/cart/add', { variant_id: variantId, quantity });
export const getCart = () => api.get('/cart');
export const updateCartItem = (itemId, quantity) => api.put(`/cart/${itemId}`, { quantity });
export const removeCartItem = (itemId) => api.delete(`/cart/${itemId}`);

export default api;