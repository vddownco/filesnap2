import {Controller} from '@hotwired/stimulus';
import Modal from '../scripts/modal';
import executeAfterTurboVisitRender from '../scripts/turbo_init';
import notify from '../scripts/notification';

export default class extends Controller {
    static targets = ['snap']

    selectedIds = []

    select(event) {
        const snap = event.currentTarget.parentNode

        if (snap.classList.contains('selected')) {
            this.removeFromSelected(snap)
        } else {
            this.addToSelected(snap)
        }
    }

    selectAll() {
        this.snapTargets.forEach(snap => this.addToSelected(snap))
    }

    unselectAll() {
        this.snapTargets.forEach(snap => this.removeFromSelected(snap))
    }

    deleteSelected() {
        const deleteSelectedSnaps = async () => {
            (await fetch('/u/snap/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({'ids': this.selectedIds})
            })).text().then(async () => {
                const goToPreviousPage = this.selectedIds.length === this.snapTargets.length
                const url = new URL(window.location.href)

                if (true === goToPreviousPage) {
                    const currentPage = parseInt(url.searchParams.get('page'))

                    const pageToRedirect = isNaN(currentPage) || 1 === currentPage
                        ? 1
                        : currentPage - 1

                    url.searchParams.set('page', pageToRedirect.toString())
                }

                executeAfterTurboVisitRender(() => {
                    notify(
                        'success',
                        `${this.selectedIds.length} snap${this.selectedIds.length > 1 ? 's' : ''} deleted`
                    )
                })

                Turbo.cache.clear()
                Turbo.visit(url.pathname + url.search, {action: 'replace'})

            }).catch(() => {
                notify(
                    'error',
                    `An error happened`
                )
            })
        }

        const configuration = this.selectedIds.length > 0
            ? {
                content: `
                    Are you sure you want to delete ${this.selectedIds.length}
                    snap${this.selectedIds.length > 1 ? 's' : ''} ?
                `,
                buttons: [
                    {
                        label: 'Yes',
                        classes: 'button py-1 px-3 bg-red-400 select-none',
                        callback: deleteSelectedSnaps,
                        closeOnClick: true
                    },
                    {
                        label: 'No',
                        classes: 'button py-1 px-3 bg-blue-300 select-none',
                        closeOnClick: true
                    }
                ]
            }
            : {
                content: 'If you wish to delete one or more snaps, select them by clicking on them and click again on the "Delete selected" button',
                buttons: [
                    {
                        label: 'Understood !',
                        classes: 'button py-1 px-3 bg-blue-300 select-none',
                        closeOnClick: true
                    }
                ]
            }

        const modal = new Modal(configuration)
        modal.open()
    }

    copyLinkToClipboard(event) {
        const animation = (clickedButton) => {
            const position = clickedButton.getBoundingClientRect()

            const notification = document.createElement('div')
            notification.innerText = 'Link copied !'
            notification.classList.add(
                'absolute', 'bg-stone-800', 'text-stone-50', 'z-30', 'py-1.5', 'px-2', 'rounded-lg',
                'font-bold', 'select-none'
            )

            notification.classList.add('link-in-left')

            notification.style.top = `${document.documentElement.scrollTop + position.top + 5}px`
            notification.style.left = `${position.left - 118}px`

            document.querySelector('body').appendChild(notification)

            setTimeout(() => {
                notification.classList.remove('link-in-left')
                notification.classList.add('link-out-left')
            }, 750)

            setTimeout(() => {
                notification.remove()
            }, 1250)
        }

        const clickedButton = event.currentTarget
        const link = clickedButton.parentNode.parentNode.querySelector('a').href

        navigator.clipboard.writeText(link).then(
            () => {
                animation(clickedButton)
            },
            () => {
                notify(
                    'error',
                    `An error happened`
                )
            }
        )
    }

    addToSelected(snap) {
        const id = snap.dataset.snapId

        snap.classList.add('selected')

        if (false === this.selectedIds.includes(id)) {
            this.selectedIds = [...this.selectedIds, id]
        }
    }

    removeFromSelected(snap) {
        const id = snap.dataset.snapId

        snap.classList.remove('selected')

        this.selectedIds = this.selectedIds.filter(i => i !== id)
    }
}