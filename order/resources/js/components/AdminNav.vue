<template>
<nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-success-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="/assets/img/avatars/avatar.png" alt="Avatar" class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <router-link :to="{ name: 'admin-account'}" class="dropdown-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="/assets/img/avatars/avatar.png" alt="Avatar" class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-shrink">
                                    <span v-if="user.display_name" class="fw-semibold d-block" >{{user.display_name}}</span>
                                    <span v-else class="fw-semibold d-block align-middle" >Tài khoản chưa đăng nhập</span>
                                </div>
                            </div>
                        </router-link>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <router-link :to="{ name: 'admin-account'}" class="dropdown-item">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">Tài Khoản</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'account-resetpassword'}" class="dropdown-item">
                            <i class='bx bx-key me-2'></i>
                            <span class="align-middle">Đổi mật khẩu</span>
                        </router-link>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item">
                            <i class="bx bx-power-off me-2"></i>
                            <span @click="logout" class="align-middle">Đăng Xuất</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
</template>

<script>
import {
    defineComponent,
    ref,
    onMounted
} from 'vue';
import {
    useToast
} from 'vue-toast-notification';
import axios from 'axios';
import {
    inject
} from 'vue';
import { useRouter } from 'vue-router';
export default defineComponent({
    setup() {
        const user = ref({
            username: "",
            email: "",
            phone: "",
            customerCode: "",
        });
        const globalState = inject('globalState');
        const baseUrl = globalState.baseUrl;
        const toast = useToast();
        const password = ref({
            currentPassword: "",
            newPassword: "",
            confirmNewPassword: "",
        });

        const router = useRouter();
        const fetchUserData = async () => {
            try {
                const token = localStorage.getItem('token');
                if (!token) {
                    throw new Error('Token not found');
                }
                const response = await axios.get(`${baseUrl}/api/profile`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });
                user.value = response.data;
            } catch (error) {
                console.error(error);
                toast.error('Người dùng chưa đăng nhập');
            }
        };
        const logout = () => {
        const token = localStorage.getItem('token');
        axios
            .post(`${baseUrl}/api/logout`, null, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            })
            .then((response) => {
                console.log(response.data.message);
                toast.success("Đăng xuất thành công");
                router.push('/login')
            })
            .catch((error) => {
                console.error(error);
            });
        };

        onMounted(() => {
            fetchUserData();
            logout
        });

        return {
            user,
            password,
            logout,
        };
    },
});
</script>
