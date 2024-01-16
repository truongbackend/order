<template>
<div class="row">
    <div class="col-md-6">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Khách hàng <i class="bx bx-chevron-right" style="font-size: 40px;"></i></span> Danh sách khách hàng</h4>
    </div>
    <div class="col-md-6">
        <router-link :to="{ name: 'admin-customer-create' }" v-if="hasPermission('customer.create')">
            <button type="button" class="btn btn-primary float-end"> <i class='bx bx-plus'></i>Thêm khách hàng</button>
        </router-link>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="input-group input-group-merge">
            <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
            <input class="form-control" id="html5-search-input" v-model="searchKeyword" @input="searchProducts" type="text" placeholder="Nhập thông tin tìm kiếm...">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive text-nowrap">
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mã KH</th>
                        <th>Tên KH</th>
                        <th>Điện thoại</th>
                        <th>Khu vực</th>
                        <th class="content-table-customer">Địa chỉ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(customer, index) in customers" :key="customer.ID">
                        <td>{{ index + 1 }}</td>
                        <td><i class="fab fa-react fa-lg text-info me-3"></i>
                            <router-link :to="{name: 'admin-customer-detail',params: { customer: customer.ID },}">{{ customer.customer_code }}</router-link>
                        </td>
                        <td><i class="fab fa-react fa-lg text-info me-3"></i>
                            <router-link :to="{name: 'admin-customer-detail',params: { customer: customer.ID },}" class="content-table">{{ customer.customer_name }}</router-link>
                        </td>
                        <td>{{ customer.customer_phone }}</td>
                        <td>{{ stores.find((x) =>x.ID == customer.store_id)?.store_name
                                    }}</td>
                        <td class="content-table-customer">{{ customer.customer_addr }}</td>
                        <td>
                            <router-link :to="{ name: 'admin-customer-update', params: { id: customer.ID } }" v-if="hasPermission('customer.edit')">
                                <i class="bx bxs-edit me-1 text-dark"></i>
                            </router-link>
                            <a @click="deleteCustomer(customer.ID)" v-if="hasPermission('customer.delete')">
                                <i class="bx bx-trash me-1 text-dark"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <nav aria-label="Page navigation mt-4">
                <ul class="pagination justify-content-end">
                    <li class="page-item prev">
                        <a class="page-link" href="javascript:void(0); " @click="changePage('first')">
                            <i class="tf-icon bx bx-chevrons-left"></i>
                        </a>
                    </li>
                    <li class="page-item prev">
                        <a class="page-link" href="javascript:void(0);" @click="changePage(currentPage - 1)">
                            <i class="tf-icon bx bx-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="page in displayedPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                        <a class="page-link" href="javascript:void(0);" @click="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item next">
                        <a class="page-link" href="javascript:void(0);" @click="changePage(currentPage + 1)">
                            <i class="tf-icon bx bx-chevron-right"></i>
                        </a>
                    </li>
                    <li class="page-item next">
                        <a class="page-link" href="javascript:void(0);" @click="changePage('last')">
                            <i class="tf-icon bx bx-chevrons-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

</div>
</template>

<script>
import axios from 'axios';
import {
    defineComponent,
    ref,
    watch,
    onMounted,
    computed
} from 'vue';
import {
    useRouter
} from 'vue-router';
import {
    useToast
} from 'vue-toast-notification';
import {
    inject
} from 'vue';
import {
    fetchUserPermission
} from '@/utils/permission.js';
export default defineComponent({
    setup() {
        const globalState = inject('globalState');
        const baseUrl = globalState.baseUrl;
        const customers = ref([]);
        const router = useRouter();
        const toast = useToast();
        const currentPage = ref(1);
        const itemsPerPage = 10;
        const totalPages = ref(0);
        const userPermission = ref([]);
        const searchKeyword = ref('');
        const searchTimer = ref(null);
        const stores = ref([]);
        const maxDisplayedPages = 4;
        const maxDisplayedLeft = Math.floor(maxDisplayedPages / 2);
        const maxDisplayedRight = Math.ceil(maxDisplayedPages / 2);
        const displayedPages = computed(() => {
            let startPage = Math.max(currentPage.value - maxDisplayedLeft, 1);
            let endPage = Math.min(currentPage.value + maxDisplayedRight, totalPages.value);
            if (endPage - startPage + 1 < maxDisplayedPages) {
                if (currentPage.value <= maxDisplayedLeft) {
                    endPage = Math.min(endPage + maxDisplayedPages - (endPage - startPage + 1), totalPages.value);
                } else {
                    startPage = Math.max(startPage - (maxDisplayedPages - (endPage - startPage + 1)), 1);
                }
            }
            return Array.from({
                length: endPage - startPage + 1
            }, (_, i) => startPage + i);
        });
        const getCustomers = () => {
            axios
                .get(`${baseUrl}/api/customers`, {
                    params: {
                        page: currentPage.value,
                        per_page: itemsPerPage,
                        keyword: searchKeyword.value,
                    },
                })
                .then((response) => {
                    customers.value = response.data.customers.data;
                    stores.value = response.data.stores;
                    totalPages.value = response.data.customers.last_page;
                })
                .catch((error) => {
                    console.log(error);
                });
        };

        const performSearch = () => {
            clearTimeout(searchTimer.value);
            searchTimer.value = setTimeout(() => {
                currentPage.value = 1;
                getCustomers();
            }, 300);
        };

        const changePage = (page) => {
            if (page === 'first') {
                currentPage.value = 1;
            } else if (page === 'last') {
                currentPage.value = totalPages.value;
            } else if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
            }
            getCustomers();
        };

        const deleteCustomer = (customerId) => {
            axios
                .delete(`${baseUrl}/api/customers/${customerId}`)
                .then(() => {
                    toast.success('Xoá khách hàng thành công');
                    customers.value = customers.value.filter(
                        (customer) => customer.ID !== customerId
                    );
                })
                .catch((error) => {
                    console.log(error);
                    toast.error('Failed to delete user');
                });
        };
        onMounted(async () => {
            try {
                userPermission.value = await fetchUserPermission();
            } catch (error) {
                console.error('Error fetching user roles:', error);
            }
        });

        function hasPermission(args) {
            return Object.values(userPermission.value).includes(args);
        }
        watch(searchKeyword, performSearch);
        getCustomers();

        return {
            customers,
            deleteCustomer,
            searchKeyword,
            currentPage,
            totalPages,
            changePage,
            hasPermission,
            userPermission,
            stores,
            itemsPerPage,
            displayedPages,
            currentPage,
            totalPages,
        };
    },
});
</script>
