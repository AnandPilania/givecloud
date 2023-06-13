<template>
    <div class="messenger-console">
        <div class="phone">
            <i>Speaker</i>
            <b>Camera</b>
            <div class="flex h-full flex-col items-stretch">
                <div
                    class="w-full pt-12 pb-4 flex flex-col items-center justify-center bg-gray-100 border-b border-solid border-gray-300"
                >
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-white">
                        <img
                            class="w-8 h-auto"
                            alt="Givecloud Logo"
                            src="https://cdn.givecloud.co/static/branding/logo/primary/logo_mark/full_color/digital/givecloud-logo-mark-full-color-rgb.png"
                        />
                    </div>
                    <div class="pt-2 text-xs leading-tight">{{ siteName }}</div>
                </div>
                <div class="flex-grow">
                    <ul class="h-96 overflow-y-auto p-2" id="message-timeline" ref="timeline">
                        <li
                            v-for="message in messages"
                            class="flex items-end my-2"
                            :class="{ 'flex-row-reverse': message.isMine }"
                        >
                            <div v-if="!message.isMine" class="mb-3 px-1">
                                <img
                                    class="w-12 h-auto"
                                    alt="Givecloud Logo"
                                    src="https://cdn.givecloud.co/static/branding/logo/primary/logo_mark/full_color/digital/givecloud-logo-mark-full-color-rgb.png"
                                />
                            </div>
                            <div
                                class="message flex justify-end max-w-full relative mx-3 p-2 rounded-lg"
                                :class="{ 'bg-gray-200': !message.isMine, ' bg-blue-500 text-white': message.isMine }"
                            >
                                <div
                                    class="absolute h-2 w-2 rotate-45 bottom-0 mb-4"
                                    :class="{
                                        'bg-gray-200 -left-1': !message.isMine,
                                        'bg-blue-500 -right-1': message.isMine,
                                    }"
                                ></div>
                                <div class="whitespace-pre-wrap">{{ message.text }}</div>
                                <img
                                    v-if="message.attachment.type === 'image'"
                                    class="max-w-full ChatLog__message__image"
                                    :src="message.attachment.url"
                                />
                                <video
                                    v-else-if="message.attachment.type === 'video'"
                                    controls
                                    class="max-w-full ChatLog__message__image"
                                    height="160"
                                    autoplay=""
                                >
                                    <source :src="message.attachment.url" type="video/mp4" />
                                </video>
                                <audio
                                    v-else-if="message.attachment.type === 'audio'"
                                    controls
                                    class="max-w-full ChatLog__message__image"
                                    autoplay=""
                                >
                                    <source :src="message.attachment.url" type="audio/mp3" />
                                </audio>
                            </div>
                            <div v-if="message.original.type === 'actions'">
                                <div
                                    class="btn"
                                    v-for="action in message.original.actions"
                                    @click="performAction(action.value, message.original)"
                                >
                                    <img v-if="action.image_url" :src="action.image_url" style="max-height: 25px" />
                                    {{ action.text }}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-100 border-t border-solid border-gray-300 p-4">
                    <input type="file" id="attachment" value="Attachment" />
                    <div class="flex">
                        <label
                            class="rounded-full bg-gray-200 outline-none h-10 w-14 flex items-center justify-center"
                            for="attachment"
                        >
                            <svg
                                class="w-4"
                                aria-hidden="true"
                                focusable="false"
                                data-prefix="fal"
                                data-icon="paperclip"
                                role="img"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"
                            >
                                <path
                                    fill="currentColor"
                                    d="M149.106 512c-33.076 0-66.153-12.59-91.333-37.771-50.364-50.361-50.364-132.305-.002-182.665L319.842 29.498c39.331-39.331 103.328-39.331 142.66 0 39.331 39.332 39.331 103.327 0 142.657l-222.63 222.626c-28.297 28.301-74.347 28.303-102.65 0-28.3-28.301-28.3-74.349 0-102.649l170.301-170.298c4.686-4.686 12.284-4.686 16.97 0l5.661 5.661c4.686 4.686 4.686 12.284 0 16.971l-170.3 170.297c-15.821 15.821-15.821 41.563.001 57.385 15.821 15.82 41.564 15.82 57.385 0l222.63-222.626c26.851-26.851 26.851-70.541 0-97.394-26.855-26.851-70.544-26.849-97.395 0L80.404 314.196c-37.882 37.882-37.882 99.519 0 137.401 37.884 37.881 99.523 37.882 137.404.001l217.743-217.739c4.686-4.686 12.284-4.686 16.97 0l5.661 5.661c4.686 4.686 4.686 12.284 0 16.971L240.44 474.229C215.26 499.41 182.183 512 149.106 512z"
                                ></path>
                            </svg>
                        </label>
                        <input
                            ref="message"
                            class="rounded-full mx-4 w-full shadow h-10 py-3 px-4 outline-none"
                            type="text"
                            @keyup.enter="sendMessage"
                            v-model="newMessage"
                            placeholder="Send Message..."
                        />
                        <button
                            @click="sendMessage"
                            class="rounded-full outline-none cursor-pointer bg-blue-500 text-white h-10 w-14 flex items-center justify-center"
                        >
                            <svg
                                class="w-4"
                                aria-hidden="true"
                                focusable="false"
                                data-prefix="fal"
                                data-icon="paper-plane"
                                role="img"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 512 512"
                            >
                                <path
                                    fill="currentColor"
                                    d="M464 4.3L16 262.7C-7 276-4.7 309.9 19.8 320L160 378v102c0 30.2 37.8 43.3 56.7 20.3l60.7-73.8 126.4 52.2c19.1 7.9 40.7-4.2 43.8-24.7l64-417.1C515.7 10.2 487-9 464 4.3zM192 480v-88.8l54.5 22.5L192 480zm224-30.9l-206.2-85.2 199.5-235.8c4.8-5.6-2.9-13.2-8.5-8.4L145.5 337.3 32 290.5 480 32l-64 417.1z"
                                ></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center mb-24">
            <h3 class="text-lg">Change Sender's Phone Number</h3>
            <p class="max-w-sm text-center text-gray-600">
                If you've restricted a conversation to be available to select recipients, you can test that
                functionality by entering the number you'd like to test as the sender.
            </p>
            <div class="flex pt-4">
                <input
                    class="rounded-full p-4 w-72 text-center h-10 py-3 px-4"
                    type="text"
                    v-model="userId"
                    placeholder="Sender's Phone # (ex. +16137371111)"
                />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        siteName: String,
    },
    data() {
        return {
            messages: [],
            newMessage: null,
            userId: null,
        };
    },
    mounted() {
        let control = document.getElementById('attachment');
        control.addEventListener(
            'change',
            () => {
                const file = control.files[0];
                if (file) {
                    let type = file.type.split('/')[0];
                    const reader = new FileReader();
                    if (type !== 'video' && type !== 'audio' && type !== 'image') {
                        type = 'file';
                    }
                    const onReaderLoad = () => {
                        this._addMessage(
                            null,
                            {
                                type,
                                url: reader.result,
                            },
                            true
                        );
                        this.callAPI(null, false, type);
                        control.value = '';
                    };
                    reader.addEventListener('load', onReaderLoad, false);
                    reader.readAsDataURL(file);
                }
            },
            false
        );
        this.$refs.message.focus();
    },
    methods: {
        callAPI(text, interactive = false, attachment = null, callback) {
            let data = new FormData();
            data.append('driver', 'web');
            data.append('userId', this.userId || '+16137371111');
            data.append('message', text);
            data.append('attachment', null);
            data.append('interactive', null);
            data.append('attachment_data', document.getElementById('attachment').files[0]);
            axios.post('/webhook/messenger', data).then((response) => {
                const messages = response.data.messages || [];
                messages.forEach((msg) => {
                    this._addMessage(msg.text, msg.attachment, false, msg);
                });
                if (callback) {
                    callback(response.data);
                }
            });
        },
        performAction(value, message) {
            this.callAPI(value, true, null, (response) => {
                message.actions = null;
            });
        },
        _addMessage(text, attachment, isMine, original = {}) {
            this.messages.push({
                isMine,
                user: isMine ? '👨' : '🤖',
                text,
                original,
                attachment: attachment || {},
            });
            this.$nextTick(() => {
                this.$refs.timeline.scrollTop = this.$refs.timeline.scrollHeight;
            });
        },
        sendMessage() {
            let messageText = this.newMessage;
            this.newMessage = '';
            if (messageText === 'clear') {
                this.messages = [];
                return;
            }
            this._addMessage(messageText, null, true);
            this.callAPI(messageText);
            this.$refs.message.focus();
        },
    },
};
</script>

<style lang="scss">
.messenger-console {
    input[type='file'] {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
    #message-timeline {
        min-height: 400px;
    }
}

.phone {
    position: relative;
    margin: 40px auto;
    width: 360px;
    overflow: hidden;
    background-color: white;
    border-radius: 40px;
    box-shadow: 0px 0px 0px 11px #1f1f1f, 0px 0px 0px 13px #191919, 0px 0px 0px 20px #111;

    &:before,
    &:after {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    // home button indicator
    &:after {
        bottom: 7px;
        width: 140px;
        height: 4px;
        background-color: #f2f2f2;
        border-radius: 10px;
    }

    // frontal camera/speaker frame
    &:before {
        top: 0px;
        width: 56%;
        height: 30px;
        background-color: #1f1f1f;
        border-radius: 0px 0px 40px 40px;
    }

    > i,
    > b,
    s,
    span {
        position: absolute;
        display: block;
        color: transparent;
    }

    // speaker
    i {
        top: 0px;
        left: 50%;
        transform: translate(-50%, 6px);
        height: 8px;
        width: 15%;
        background-color: #101010;
        border-radius: 8px;
        box-shadow: inset 0px -3px 3px 0px rgba(256, 256, 256, 0.2);
    }

    // camera
    b {
        left: 10%;
        top: 0px;
        transform: translate(180px, 4px);
        width: 12px;
        height: 12px;
        background-color: #101010;
        border-radius: 12px;
        box-shadow: inset 0px -3px 2px 0px rgba(256, 256, 256, 0.2);

        &:after {
            content: '';
            position: absolute;
            background-color: #2d4d76;
            width: 6px;
            height: 6px;
            top: 2px;
            left: 2px;
            top: 3px;
            left: 3px;
            display: block;
            border-radius: 4px;
            box-shadow: inset 0px -2px 2px rgba(0, 0, 0, 0.5);
        }
    }
}
</style>
