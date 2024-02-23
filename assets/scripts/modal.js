import * as tingle from 'tingle.js'

export default class Modal {

    constructor(configuration) {
        this.modal = new tingle.modal({
            footer: true,
            stickyFooter: true,
            closeMethods: [],
            closeLabel: 'Close',
            cssClass: [],
            onClose: () => {
                this.modal.destroy()
            }
        });

        this.modal.setContent(configuration.content)

        configuration.buttons?.forEach(buttonConfiguration => {
            this.addButton(buttonConfiguration)
        })
    }

    addButton(configuration) {
        let callback = () => {
        }

        if (configuration?.closeOnClick === true) {
            if (typeof configuration?.callback === 'function') {
                callback = () => {
                    configuration?.callback()
                    this.close()
                }
            } else {
                callback = () => {
                    this.close()
                }
            }
        } else if (typeof configuration?.callback === 'function') {
            callback = configuration?.callback()
        }

        this.modal.addFooterBtn(configuration?.label, configuration?.classes, callback)
    }

    open() {
        this.modal.open()
    }

    close() {
        this.modal.close()
    }
}