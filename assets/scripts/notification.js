export default function notify(type, message) {
    const notification = document.createElement('div')
    let classes

    switch (type) {
        case 'success':
            classes = ['bg-green-300', 'text-stone-800']
            break
        case 'error':
            classes = ['bg-red-400', 'text-stone-800']
            break
        default:
            classes = ['bg-blue-300', 'text-stone-800']
    }

    notification.classList.add(
        'z-30', 'py-1.5', 'px-2', 'rounded-lg', 'font-bold', 'select-none', 'notification', ...classes
    )

    notification.setAttribute('data-turbo-temporary', 'true')
    notification.innerText = message
    document.querySelector('body').appendChild(notification)

    setTimeout(() => {
        notification.classList.add('show')
    }, 10)

    setTimeout(() => {
        notification.classList.remove('show')
    }, 2000)

    setTimeout(() => {
        notification.remove()
    }, 2500)
}