import axios from 'axios';
import { Flatfile } from '@flatfile/sdk';

const importer = async (token) => {

    const element = document.getElementById('flatFileImport')
    const customFields = JSON.parse(element.dataset.customFields || "[]");
    const ffToken = token || element.dataset.token;

    const onCloseEvent = function () {
        document.querySelectorAll('.flatfile-close').forEach((node) =>{
            node.removeEventListener('click', onCloseEvent);
        });

        if (! confirm('The import process is not completely finished. Are you sure you want to exit?')) {
            importer(ffToken);
            return;
        }

        if (! element.dataset.refresh){
            return;
        }

        axios.get(element.dataset.refresh)
            .then(({data}) => {
                element.setAttribute('data-token', data?.token);
            }).catch(() => {
                window.location.reload();
            });
    }

    await Flatfile.requestDataFromUser({
        token: token,
        customFields: customFields,
        theme: {
            submitCompleteText: 'Make sure you press the submit button to finalize your import!',
        },
        onInit(){
            document.querySelectorAll('.flatfile-close').forEach((node) =>{
                node.addEventListener('click', onCloseEvent);
            });
        },
        onData(chunk, next) {
            next();
        },
        onComplete() {
            document.querySelectorAll('.flatfile-close').forEach((node) =>{
                node.removeEventListener('click', onCloseEvent);
            });
            window.toastr.success('Processing import... It may take a couple of minutes for the import to be completed.');
        }
    });
}

export default importer
