const disableTurboDrive = document.querySelector('meta[name="disable-turbo-drive"]')

if (disableTurboDrive) {
    Turbo.session.drive = false
}

document.documentElement.addEventListener('turbo:before-cache', () => {
    const snaps = document.querySelectorAll('.snap.selected')

    snaps.forEach(snap => {
        snap.classList.remove('selected')
    })
})

export default function executeAfterTurboVisitRender(callback) {
    const unbindingEventListenerCallback = () => {
        callback()
        document.documentElement.removeEventListener('turbo:render', unbindingEventListenerCallback)
    }

    document.documentElement.addEventListener('turbo:render', unbindingEventListenerCallback)
}