import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'navigation']

    toggle() {
        if (this.menuTarget.classList.contains('open')) {
            this.navigationTargets.forEach(link => {
                if ('' === link.href) {
                    return
                }
                const url = new URL(link.href)
                url.searchParams.delete('open_menu')
                link.href = url.href
            })
            this.menuTarget.classList.remove('open')
        } else {
            this.navigationTargets.forEach(link => {
                if ('' === link.href) {
                    return
                }
                const url = new URL(link.href)
                url.searchParams.append('open_menu', '1')
                link.href = url.href
            })
            this.menuTarget.classList.add('open')
        }
    }
}