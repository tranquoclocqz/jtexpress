<script>
import "@fullcalendar/core/vdom"; // solves problem with Vite
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import axiosClient from "../src/axios/axiosClient";
import moment from "moment";
export default {
    components: {
        FullCalendar, // make the <FullCalendar> tag available
    },
    data() {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin],
                initialView: "dayGridMonth",
                dateClick: this.handleDateClick,
                eventClick: this.handleEventClick,
                eventDrop: this.handleEventDrop,
                editable: true,
                events: [],
                customButtons: {
                    prev: {
                        click: () => {
                            const calendarApi =
                                this.$refs.fullCalendar.getApi();
                            calendarApi.prev();
                            const date = calendarApi.getDate();
                            this.currentMonth = moment(date).format("MM");
                            this.currentYear = moment(date).format("YYYY");
                            this.fetch();
                        },
                    },
                    next: {
                        click: () => {
                            const calendarApi =
                                this.$refs.fullCalendar.getApi();
                            calendarApi.next();
                            const date = calendarApi.getDate();
                            this.currentMonth = moment(date).format("MM");
                            this.currentYear = moment(date).format("YYYY");
                            this.fetch();
                        },
                    },
                    today: {
                        'text': 'Today',
                        click: () => {
                            const calendarApi =
                                this.$refs.fullCalendar.getApi();
                            calendarApi.today();
                            const date = calendarApi.getDate();
                            this.currentMonth = moment(date).format("MM");
                            this.currentYear = moment(date).format("YYYY");
                            this.fetch();
                        }
                    }
                },
            },
            date: "",
            title: "",
            id: 0,
            errors: "",
            currentMonth: moment().format("MM"),
            currentYear: moment().format("YYYY"),
        };
    },
    created() {
        this.fetch();
    },
    methods: {
        fetch: async function () {
            this.calendarOptions.events = await axiosClient.get(
                "/calendar",
                { params: { year: this.currentYear, month: this.currentMonth } }
            );
        },
        updateEvent: async function (formData) {
            return axiosClient.post(
                `/calendar/${this.id}`,
                formData
            );
        },
        deleteEvent: async function () {
            let bodyFormData = new FormData();
            bodyFormData.append("_method", "delete");
            axiosClient
                .post(
                    `/calendar/${this.id}`,
                    bodyFormData
                )
                .then((response) => {
                    if (response.success === "success") {
                        this.currentMonth = moment(this.date).format("MM");
                        this.currentYear = moment(this.date).format("YYYY");
                        this.resetModal();
                        this.hideModalEdit();
                        this.fetch();
                    }
                });
        },
        handleEventDrop: function (info) {
            this.id = info.event.id;
            let bodyFormData = new FormData();
            bodyFormData.append("_method", "patch");
            bodyFormData.append(
                "date",
                moment(info.event.start).format("YYYY-MM-DD")
            );
            this.updateEvent(bodyFormData);
        },
        handleEventClick: function (info) {
            this.title = info.event.title;
            this.date = moment(info.event.start).format("YYYY-MM-DD");
            this.id = info.event.id;
            this.openModalEdit();
        },
        handleDateClick: function (arg) {
            this.openModalCreate();
            this.date = arg.dateStr;
        },
        resetModal: function () {
            this.date = this.title = "";
            this.id = 0;
        },
        createEvent: function (e) {
            e.preventDefault();
            let bodyFormData = new FormData();
            bodyFormData.append("title", this.title);
            bodyFormData.append("date", this.date);
            axiosClient
                .post("/calendar", bodyFormData)
                .then((response) => {
                    if (response.success === "success") {
                        this.currentMonth = moment(this.date).format("MM");
                        this.currentYear = moment(this.date).format("YYYY");
                        this.resetModal();
                        this.hideModalCreate();
                        this.fetch();
                    }
                    if (response.success === "fail") {
                        this.errors = response.errors;
                    }
                });
        },
        editEvent: function (e) {
            e.preventDefault();
            let bodyFormData = new FormData();
            bodyFormData.append("_method", "patch");
            bodyFormData.append("title", this.title);
            bodyFormData.append("date", this.date);
            this.updateEvent(bodyFormData).then((response) => {
                if (response.success === "success") {
                    this.currentMonth = moment(this.date).format("MM");
                    this.currentYear = moment(this.date).format("YYYY");
                    this.resetModal();
                    this.hideModalEdit();
                    this.fetch();
                }
                if (response.success === "fail") {
                    this.errors = response.errors;
                }
            });
        },
        openModalCreate: function () {
            this.$refs["my-modal"].show();
        },
        openModalEdit: function () {
            this.$refs["my-modal-edit"].show();
        },
        hideModalCreate: function () {
            this.$refs["my-modal"].hide();
        },
        hideModalEdit: function () {
            this.$refs["my-modal-edit"].hide();
        },
    },
    mounted: function () {},
};
</script>
<template>
    <div>
        <FullCalendar :options="calendarOptions" ref="fullCalendar" />
        <b-modal
            ref="my-modal"
            title="Tạo sự kiện"
            @hidden="resetModal"
        >
            <div class="d-block">
                <form ref="form" methods="POST" v-on:submit="createEvent">
                    <div class="form-group">
                        <input
                            class="form-control"
                            placeholder="Tiêu đề sự kiện"
                            v-model="title"
                        />
                        <input
                            class="form-control"
                            type="hidden"
                            v-model="date"
                        />
                    </div>
                    <div class="form-group" v-if="this.errors.length != 0">
                        <small class="text-danger">{{ this.errors }}</small>
                    </div>
                </form>
            </div>
            <template #modal-footer="{ ok, hide }">
                <b-button
                    size="sm"
                    variant="outline-secondary"
                    v-on:click="hideModalCreate"
                >
                    Đóng
                </b-button>               
                <b-button size="sm" variant="success" v-on:click="createEvent">
                    Hoàn tất
                </b-button>
            </template>
        </b-modal>

        <b-modal
            ref="my-modal-edit"
            title="Cập nhật sự kiện"
            @hidden="resetModal"
        >
            <div class="d-block">
                <form ref="form" methods="POST" v-on:submit="editEvent">
                    <div class="form-group">
                        <input
                            class="form-control"
                            placeholder="Tiêu đề sự kiện"
                            v-model="title"
                        />
                        <input
                            class="form-control"
                            type="hidden"
                            v-model="date"
                        />
                    </div>
                    <div class="form-group" v-if="this.errors.length != 0">
                        <small class="text-danger">{{ this.errors }}</small>
                    </div>
                </form>
            </div>
            <template #modal-footer="{ ok, cancel, hide }">
                <b-button
                    size="sm"
                    variant="outline-secondary"
                    v-on:click="hideModalEdit"
                >
                    Đóng
                </b-button>
                <b-button size="sm" variant="danger" v-on:click="deleteEvent">
                    Xóa
                </b-button>
                <b-button size="sm" variant="success" v-on:click="editEvent">
                    Cập nhật
                </b-button>
            </template>
        </b-modal>
    </div>
</template>
