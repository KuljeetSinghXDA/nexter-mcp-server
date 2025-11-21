/**
 * Scroll Navigation
*/
document.addEventListener('DOMContentLoaded', () => {
    tpscrollNav(document);
});

function tpscrollNav(doc) {
    let allScrollNav = document.querySelectorAll('.tpgb-scroll-nav'),
         winScroll;
    if (allScrollNav) {
        allScrollNav.forEach((sn) => {
            let navItems = sn.querySelectorAll('.tpgb-scroll-nav-item');
            if (navItems) {
                navItems.forEach((ni) => {
                    ni.addEventListener('click', (e) => {
                        e.preventDefault();

                        // Remove 'active' class from previously active item
                        let closeMain = e.currentTarget.closest('.tpgb-scroll-nav');
                        let actNavItem = closeMain.querySelector('.tpgb-scroll-nav-item.active');
                        if (actNavItem) {
                            actNavItem.classList.remove('active');
                        }

                        // Add 'active' class to the clicked item
                        e.currentTarget.classList.add('active');

                        // Get the section ID from the clicked link and scroll to it
                        let getSecId = e.currentTarget.getAttribute('href');
                        let getHash = getSecId.split('#')[1];

                        // Change the section view
                        clickchangesection(getHash);
                    });
                });

                window.addEventListener('scroll', ()=>{ 
                    winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    scrollchangesection(navItems, winScroll);
                });
                window.addEventListener('load', ()=>{ 
                    winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    scrollchangesection(navItems, winScroll);
                });
            }
        });
    }
}

// Function to scroll to the clicked section
function clickchangesection(sectionId) {
    let targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.scrollIntoView({
            behavior: 'smooth'
        });
    }
}

function getOffsetTop(elem) {
    let offsetTop = 0;
    while (elem) {
        offsetTop += elem.offsetTop;
        elem = elem.offsetParent;
    }
    return offsetTop;
}

function scrollchangesection(navItems, scroll){
    navItems.forEach((nav)=>{
        let getSecId = nav.href,
            getHash = getSecId.split('#')[1];
        if(getHash){
            let getSec = document.getElementById(getHash);
            if(getSec){
                let getcScroll = getOffsetTop(getSec),
                    wHeight = window.innerHeight,
                    totalHeight = getSec.offsetHeight+getcScroll;
                if(scroll >= getcScroll - wHeight &&  totalHeight > scroll){
                    navItems.forEach((item) => {
                        if(item.classList.contains('active')){
                            item.classList.remove('active')
                        }
                    });
                    nav.classList.add('active');
                }else{
                    nav.classList.remove('active');
                }
            }
        }
    });
}