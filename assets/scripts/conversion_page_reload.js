if (document.querySelector('.waiting-for-conversion')) {
    const url = new URL(window.location.href)

    window.setInterval(async () => {
        (await fetch(url.pathname, {method: 'GET'}))
            .blob()
            .then(response => {
                if (response.type !== 'text/html') {
                    window.location.reload()
                }
            })
    }, 2000)
}
