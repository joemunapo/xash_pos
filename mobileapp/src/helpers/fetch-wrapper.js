import { useAuthStore } from '@/stores/index.js';

const baseUrl = import.meta.env.VITE_API_URL;

// Helper to convert blob to base64
export function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => {
            resolve(reader.result); // This includes data:image/png;base64,...
        };
        reader.onerror = reject;
        reader.readAsDataURL(blob);
    });
}

// Token refresh state management
let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

export const fetchWrapper = {
    get: request('GET'),
    post: request('POST'),
    put: request('PUT'),
    patch: request('PATCH'),
    delete: request('DELETE'),
    postForm: requestForm('POST')
};

function requestForm(method) {
    return async (url, formData) => {
        const authStore = useAuthStore();
        const isApiUrl = url.startsWith(baseUrl);

        const requestOptions = {
            method,
            headers: {}
        };

        // Add auth header if logged in
        if (authStore.token && isApiUrl) {
            requestOptions.headers['Authorization'] = `Bearer ${authStore.token}`;
        }
        requestOptions.headers['Accept'] = 'application/json';
        // Don't set Content-Type - let browser set it with boundary for FormData

        requestOptions.body = formData;

        try {
            const response = await fetch(url, requestOptions);
            return await handleFormResponse(response, url, formData);
        } catch (error) {
            if (error.message && (error.errors || error.response)) {
                throw error;
            }

            throw {
                message: 'Network error. Please check your connection.',
                errors: {}
            };
        }
    };
}

async function handleFormResponse(response, url, formData) {
    const isJson = response.headers?.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
        const authStore = useAuthStore();

        // Handle 401 - logout and redirect
        if (response.status === 401 && authStore.token) {
            authStore.logout();
            throw {
                message: 'Session expired. Please login again.',
                errors: {}
            };
        }

        // Handle 403 Forbidden
        if (response.status === 403 && authStore.token) {
            authStore.logout();
        }

        const error = {
            message: data?.message || response.statusText,
            errors: data?.errors || {},
            response: data
        };

        throw error;
    }

    return data;
}

function request(method) {
    return async (url, body) => {
        const requestOptions = {
            method,
            headers: authHeader(url)
        };

        if (body) {
            requestOptions.headers['Content-Type'] = 'application/json';
            requestOptions.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(url, requestOptions);
            return await handleResponse(response, url, requestOptions, body);
        } catch (error) {
            // If the error has a message and either errors or response, 
            // it's likely an error we manually threw in handleResponse
            if (error.message && (error.errors || error.response)) {
                throw error;
            }

            // Real network error or fetch failed
            throw {
                message: 'Network error. Please check your connection.',
                errors: {}
            };
        }
    };
}

function authHeader(url) {
    const authStore = useAuthStore();
    const isLoggedIn = !!authStore.token;
    const isApiUrl = url.startsWith(baseUrl);

    if (isLoggedIn && isApiUrl) {
        return {
            Authorization: `Bearer ${authStore.token}`,
            'Accept': 'application/json'
        };
    } else {
        return {
            'Accept': 'application/json'
        };
    }
}

async function refreshAccessToken() {
    const authStore = useAuthStore();
    try {
        const refreshResponse = await fetch(`${baseUrl}/refresh`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authStore.token}`,
                'Accept': 'application/json'
            }
        });

        const refreshData = await refreshResponse.json();

        if (refreshResponse.ok && refreshData.access_token) {
            // Update token in store and localStorage
            authStore.token = refreshData.access_token;
            localStorage.setItem('token', refreshData.access_token);
            return refreshData.access_token;
        } else {
            throw new Error('Token refresh failed');
        }
    } catch (error) {
        throw error;
    }
}

async function handleResponse(response, url, requestOptions, body) {
    const isJson = response.headers?.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
        const authStore = useAuthStore();

        // Handle 401 Unauthorized - try to refresh token
        if (response.status === 401 && authStore.token) {
            if (!isRefreshing) {
                isRefreshing = true;

                try {
                    // Attempt to refresh token
                    await refreshAccessToken();
                    processQueue(null, authStore.token);
                    isRefreshing = false;

                    // Retry original request with new token
                    const newRequestOptions = {
                        ...requestOptions,
                        headers: authHeader(url)
                    };

                    if (body) {
                        newRequestOptions.headers['Content-Type'] = 'application/json';
                        newRequestOptions.body = JSON.stringify(body);
                    }

                    const retryResponse = await fetch(url, newRequestOptions);
                    return await handleResponse(retryResponse, url, newRequestOptions, body);
                } catch (refreshError) {
                    processQueue(refreshError, null);
                    isRefreshing = false;
                    authStore.logout();

                    const error = {
                        message: 'Session expired. Please login again.',
                        errors: {},
                        response: data
                    };
                    throw error;
                }
            } else {
                // Queue this request while token is being refreshed
                return new Promise((resolve, reject) => {
                    failedQueue.push({ resolve, reject });
                });
            }
        }

        // Handle 403 Forbidden
        if (response.status === 403 && authStore.token) {
            authStore.logout();
        }

        const error = {
            message: data?.message || response.statusText,
            errors: data?.errors || {},
            response: data // Include full response data for special cases
        };

        throw error;
    }

    return data;
}
