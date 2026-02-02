import { defineStore } from 'pinia';
import { fetchWrapper } from '../helpers/index.js';
import { router } from '@/router/index.js';
import { db } from '@/utils/indexeddb';
import { hashPin } from '@/utils/crypto';
import { useAlertStore, useNetworkStore } from '@/stores/index.js';

const baseUrl = `${import.meta.env.VITE_API_URL}`;

export const useAuthStore = defineStore({
    id: 'auth',
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')),
        token: localStorage.getItem('token'),
        returnUrl: null,
        // Login errors
        phoneError: null,
        pinError: null,
        // Registration errors
        registerErrors: {
            first_name: null,
            last_name: null,
            phone_number: null,
            email: null,
            pin: null,
            tcs_accepted: null,
        },
        loading: false,
        biometric_enabled: false,
        account_locked_until: null,
        failed_attempts: 0
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(phone_number, pin) {
            this.loading = true;
            this.phoneError = null;
            this.pinError = null;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/pos/login`, {
                    phone_number,
                    pin
                });

                // Store user and token
                this.user = response.user;
                this.token = response.token;
                this.biometric_enabled = response.user?.biometric_enabled || false;

                // Persist to localStorage
                localStorage.setItem('user', JSON.stringify(response.user));
                localStorage.setItem('token', response.token);

                // Cache user for offline login
                const pinHash = await hashPin(pin);
                await db.users.put({
                    phone_number,
                    name: response.user.first_name + ' ' + response.user.last_name,
                    role: response.user.role,
                    pin_hash: pinHash,
                    user_data: response.user,
                    token: response.token,
                    cached_at: Date.now()
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'Login successful');

                // Navigate to role-specific dashboard
                const defaultRoute = response.user.role === 'manager'
                    ? '/manager/dashboard'
                    : '/pos/dashboard';
                router.push(this.returnUrl || defaultRoute);

            } catch (error) {
                const alertStore = useAlertStore();

                if (error.errors?.phone_number) {
                    this.phoneError = error.errors.phone_number[0];
                }
                if (error.errors?.pin) {
                    this.pinError = error.errors.pin[0];
                }

                // If no specific field errors but login failed, set pinError to the general message
                if (!this.phoneError && !this.pinError) {
                    this.pinError = error.message || 'Login failed';
                }

                alertStore.error(error.message || 'Login failed');
            } finally {
                this.loading = false;
            }
        },

        async register(first_name, last_name, phone_number, email, password, password_confirmation) {
            this.loading = true;

            // Clear previous errors
            this.registerErrors = {
                first_name: null,
                last_name: null,
                phone_number: null,
                email: null,
                password: null,
                tcs_accepted: null,
            };

            try {
                const payload = {
                    first_name,
                    last_name,
                    phone_number,
                    email,
                    password,
                    password_confirmation,
                    tcs_accepted: true
                };

                const response = await fetchWrapper.post(`${baseUrl}/register`, payload);

                return { success: true, data: response, pin: response.pin };

            } catch (error) {
                const alertStore = useAlertStore();

                // Set field-specific errors
                if (error.errors) {
                    if (error.errors.first_name) {
                        this.registerErrors.first_name = error.errors.first_name[0];
                    }
                    if (error.errors.last_name) {
                        this.registerErrors.last_name = error.errors.last_name[0];
                    }
                    if (error.errors.phone_number) {
                        this.registerErrors.phone_number = error.errors.phone_number[0];
                    }
                    if (error.errors.email) {
                        this.registerErrors.email = error.errors.email[0];
                    }
                    if (error.errors.password) {
                        this.registerErrors.password = error.errors.password[0];
                    }
                    if (error.errors.tcs_accepted) {
                        this.registerErrors.tcs_accepted = error.errors.tcs_accepted[0];
                    }
                }

                alertStore.error(error.message || 'Registration failed');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async requestOtp(phone_number) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/otp/request`, {
                    phone_number
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'OTP sent successfully');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'Failed to send OTP');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async verifyOtp(phone_number, code) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/otp/verify`, {
                    phone_number,
                    code
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'OTP verified successfully');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'OTP verification failed');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async forgotPin(phone_number) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/pin/forgot`, {
                    phone_number
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'OTP sent to your phone');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'Failed to send reset OTP');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async resetPin(phone_number, otp, new_pin, new_pin_confirmation) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/pin/reset`, {
                    phone_number,
                    otp,
                    new_pin,
                    new_pin_confirmation
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'PIN reset successful');

                router.push('/auth/login');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'PIN reset failed');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async changePin(current_pin, new_pin, new_pin_confirmation) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/pin/change`, {
                    current_pin,
                    new_pin,
                    new_pin_confirmation
                });

                const alertStore = useAlertStore();
                alertStore.success(response.message || 'PIN changed successfully');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'PIN change failed');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async requestPhoneChange(new_phone_number) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/phone/change/request`, {
                    new_phone_number
                });

                const alertStore = useAlertStore();
                alertStore.success('OTP sent to your new phone number');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'Failed to initiate phone change');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async confirmPhoneChange(new_phone_number, otp) {
            this.loading = true;

            try {
                const response = await fetchWrapper.post(`${baseUrl}/phone/change/confirm`, {
                    new_phone_number,
                    otp
                });

                // Update user in state
                if (this.user) {
                    this.user.phone_number = new_phone_number;
                    localStorage.setItem('user', JSON.stringify(this.user));
                }

                const alertStore = useAlertStore();
                alertStore.success('Phone number updated successfully');

                return { success: true, data: response };

            } catch (error) {
                const alertStore = useAlertStore();
                alertStore.error(error.message || 'Phone change confirmation failed');

                return { success: false, error: error };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await fetchWrapper.post(`${baseUrl}/pos/logout`);
                }
            } catch (error) {
                // Ignore logout errors
                console.error('Logout error:', error);
            } finally {
                // Clear state and storage
                this.user = null;
                this.token = null;
                localStorage.removeItem('user');
                localStorage.removeItem('token');
                localStorage.removeItem('pos-cart');

                router.push('/');
            }
        },

        checkAuth() {
            return !!this.token;
        },

        async loginOffline(phone_number, pin) {
            this.loading = true;
            this.phoneError = null;
            this.pinError = null;

            try {
                // Find user in local cache
                const cachedUser = await db.users.get(phone_number);

                if (!cachedUser) {
                    throw new Error('User not found on this device. Please login online first.');
                }

                // Verify PIN
                const pinHash = await hashPin(pin);
                if (cachedUser.pin_hash !== pinHash) {
                    throw new Error('Invalid PIN. Please try again.');
                }

                // Restore session from cache
                this.user = cachedUser.user_data;
                this.token = cachedUser.token;
                this.biometric_enabled = cachedUser.user_data?.biometric_enabled || false;

                // Persist to localStorage
                localStorage.setItem('user', JSON.stringify(cachedUser.user_data));
                localStorage.setItem('token', cachedUser.token);

                const alertStore = useAlertStore();
                alertStore.success('Logged in successfully (Offline Mode)');

                // Navigate to role-specific dashboard
                const defaultRoute = cachedUser.role === 'manager'
                    ? '/manager/dashboard'
                    : '/pos/dashboard';
                router.push(this.returnUrl || defaultRoute);

                return { success: true };
            } catch (error) {
                const alertStore = useAlertStore();
                this.pinError = error.message;
                alertStore.error(error.message);
                return { success: false, error: error.message };
            } finally {
                this.loading = false;
            }
        }
    }
});
