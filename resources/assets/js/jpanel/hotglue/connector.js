import toastr from 'toastr';
import axios from 'axios'

const connector = (config) => {
    const connectedButton = document.getElementById('hotglue_connected');
    const disconnectedButton = document.getElementById('hotglue_disconnected');
    const showWhenConnected = document.querySelectorAll('[data-hotglue-show-when-connected]');
    const hideWhenConnected = document.querySelectorAll('[data-hotglue-hide-when-connected]');

    const hasMounted = () => {
        return new Promise((resolve) => {
            const t = setInterval( () => {
                if(window.HotGlue?.hasMounted()){
                    clearInterval(t);
                    resolve();
                }
            }, 50)

            window.HotGlue.mount({
                "api_key": config.apiKey,
                "env_id": config.envId,
            });
        })
    }

    hasMounted().then(() => {
        window.HotGlue.preload(window.adminSpaData.accountName, config.flowId);

        window.HotGlue.setListener({
            onTargetLinked: function() {
                axios.post(config.routes.connect, {
                    target : config.target.name
                }).then(function() {
                    showWhenConnected.forEach((el) => {
                        el.classList.remove('hide');
                    });
                    hideWhenConnected.forEach((el) => {
                        el.classList.add('hide');
                    });
                    toastr.success('Connected successfully');
                }).catch(function(err) {
                    if (err.response) {
                        toastr.error(err.response.data.error);
                    }
                });
            },
            onTargetUnlinked: function() {
                axios.post(config.routes.disconnect, {
                    target : config.target.name
                }).then(function() {
                    showWhenConnected.forEach((el) => {
                        el.classList.add('hide');
                    });
                    hideWhenConnected.forEach((el) => {
                        el.classList.remove('hide');
                    });
                    toastr.success('Disconnected successfully');
                }).catch(function(err) {
                    if (err.response) {
                        toastr.error('An error occurred, please try again');
                    }
                });
            }
        });
    })

    const onClick = (e) => {
        e.preventDefault();
        window.HotGlue.link(
            window.adminSpaData.accountName,
            config.flowId,
            config.target.id,
            true,
            {
                hideBackButtons: true,
                isTarget: true,
                multipleSources: true,
            });
    }

    connectedButton.addEventListener('click', onClick);
    disconnectedButton.addEventListener('click', onClick);
}

export default connector;
