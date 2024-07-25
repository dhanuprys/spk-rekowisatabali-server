(function () {
    const accordions = document.querySelectorAll('.mantram-accordion');
    let activeId = null;

    const reRenderAccordion = () => {
        accordions.forEach(function (accordion) {
            if (!activeId || accordion.dataset.id != activeId) {
                accordion.classList.remove('is-active');
                return;
            }

            accordion.classList.add('is-active');
        });
    };

    accordions.forEach(accordion => {
        accordion.addEventListener('click', function () {
            if (activeId == this.dataset.id) {
                this.classList.remove('is-active');
                this.nextElementSibling.classList.remove('is-active');
                activeId = null;
            } else {
                activeId = this.dataset.id;
            }

            reRenderAccordion();
        });
    });
})();
