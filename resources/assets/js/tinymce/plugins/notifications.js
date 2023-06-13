
import toastr from 'toastr';

function noop() {
    // do nothing
}

function open(args, closeCallback) {
    const toastElement = toastr[args.type || 'info'](args.text);

    return {
        close: closeCallback,
        progressBar: {
            value: noop,
        },
        text: noop,
        moveTo: noop,
        moveRel: noop,
        getEl: () => toastElement,
        settings: args,
    }
}

function getNotificationManagerImpl() {
    return {
        open,
        close: noop,
        reposition: noop,
        getArgs: (theme) => theme.settings,
    };
}

export default (editor) => {
    editor.theme.getNotificationManagerImpl = getNotificationManagerImpl;
};
