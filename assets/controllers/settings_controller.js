import {Controller} from '@hotwired/stimulus';
import Modal from '../scripts/modal';
import notify from '../scripts/notification';

export default class extends Controller {
    static targets = ['apiKey']

    resetApiKey() {
        const resetUserApiKey = async () => {
            (await fetch('/u/apikey/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })).json().then(response => {
                this.apiKeyTarget.innerText = response.apikey
                notify(
                    'success',
                    'Your key has been successfully reset'
                )
            }).catch(e => {
                notify(
                    'error',
                    `An error happened`
                )
            })
        }

        const modal = new Modal({
            content: 'Are you sure you want to reset your API key ? You will need to update it wherever it was used',
            buttons: [
                {
                    label: 'Yes',
                    classes: 'button py-1 px-3 bg-red-400 select-none',
                    callback: resetUserApiKey,
                    closeOnClick: true
                },
                {
                    label: 'No',
                    classes: 'button py-1 px-3 bg-blue-300 select-none',
                    closeOnClick: true
                }
            ]
        })

        modal.open()
    }

    copyApiKey() {
        navigator.clipboard.writeText(this.apiKeyTarget.innerText).then(
            () => {
                notify(
                    'success',
                    'API key copied to clipboard !'
                )
            },
            () => {
                notify(
                    'error',
                    `An error happened`
                )
            }
        )
    }
}