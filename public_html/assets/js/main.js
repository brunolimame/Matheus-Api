//ABRIR MENU DROPDOWN AUTOMATICAMENTE
const itensMenuDropdown = document.getElementsByClassName('lista-menu dropdown');

Array.prototype.forEach.call(itensMenuDropdown, (element, index, array) => {
    element.addEventListener("click", function (event) {
        if (!element.classList.contains('aberto')) {
            Array.prototype.forEach.call(array, (e, i, a) => {
                e.classList.remove('aberto');
            });
            element.classList.add('aberto')
        } else {
            element.classList.remove('aberto')
        }
    }, false);

    element.addEventListener("mouseover", function (event) {
        if (window.screen.width > 820) {
            element.classList.add('aberto');
        }
    }, false);

    element.addEventListener("mouseout", function (event) {
        if (window.screen.width > 820) {
            element.classList.remove('aberto');
        }
    }, false);
});

//ALTERAR CAPA DO MENU DE NOTEBOOKS
const itensCapaMenuNotebook = document.getElementsByClassName('capa-menu-notebook');
const imgCapaMenuNotebook = document.getElementById("notebooks-sub-capa-img");
Array.prototype.forEach.call(itensCapaMenuNotebook, (element, index, array) => {
    element.addEventListener("mouseover", function (event) {
        imgCapaMenuNotebook.src = element.dataset.img;
        imgCapaMenuNotebook.alt = element.title;
    }, false);
});

//FIXANDO MENU
const elBody = document.getElementsByTagName('body')[0];
const topoAcaoObserver = (itens, observer) => {
    if (itens[0].isIntersecting) {
        elBody.classList.remove('menu-fixo');
    } else {
        elBody.classList.add('menu-fixo');
    }
};
const topoObserver = new IntersectionObserver(topoAcaoObserver, {
    root: null,
    rootMargin: '0px 0px 0px 0px',
    threshold: 0
})
topoObserver.observe(document.getElementById('topo-primario'));

document.addEventListener('scroll', function (e) {
    var posicaoAtualMarcador = document.getElementById('marcador-menu').getBoundingClientRect().top;
    var alturaMenu = document.getElementById('topo-geral').offsetHeight;
    if (elBody.classList.contains('menu-fixo')) {
        if (posicaoAtualMarcador > alturaMenu) {
            elBody.classList.remove('menu-fixo');
        }
    }
});
