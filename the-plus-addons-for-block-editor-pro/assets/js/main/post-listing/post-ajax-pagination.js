document.addEventListener("DOMContentLoaded", () => {
	nxtajaxPagi(document)
});

function nxtajaxPagi(doc){
	const paginationElement = doc.querySelector(".tpgb-pagination");
	if (paginationElement) {
		const pagiOptValue = paginationElement.getAttribute("data-pagi-opt");

		if (pagiOptValue === "ajax_based") {
			let isopag;
			let currentFilter = '*';
			let currentPage = 1;

			const dyoptOptValue = paginationElement.getAttribute("data-dyopt");
			const postOptValue = paginationElement.getAttribute("data-post-option");
			const dyoptOptValue1 = JSON.parse(
				paginationElement.getAttribute("data-dyopt") || "{}"
			);

			function initializeIsotope(container) {
				if (dyoptOptValue1.layout === "grid" || dyoptOptValue1.layout === "masonry" || dyoptOptValue1.layout === "metro") {
					isopag = new Isotope(container, {
						itemSelector: ".grid-item",
						resizable: true,
						sortBy: "original-order",
						layoutMode: dyoptOptValue1.layout === "masonry" ? "masonry" : "fitRows",
						transitionDuration: '0.4s',
						stagger: 30,
						hiddenStyle: {
						opacity: 0,
						transform: 'scale(0.8)'
						},
						visibleStyle: {
						opacity: 1,
						transform: 'scale(1)'
						}
					});

					imagesLoaded(container, function () {
						isopag.layout();
					});
				}
			}

			function generatePaginationNumbers(currentPage, totalPages, range = 1) {
				const showitems = (range * 2) + 1;
				let pages = [];

				pages.push(1);

				for (let i = currentPage - range; i <= currentPage + range; i++) {
					if (i > 1 && i < totalPages) {
						pages.push(i);
					}
				}

				if (totalPages > 1) {
					pages.push(totalPages);
				}

				pages = [...new Set(pages)].sort((a, b) => a - b);
				return pages;
			}

			function updatePaginationUI(currentPage, totalPages) {
				const range = 1;
				const pages = generatePaginationNumbers(currentPage, totalPages, range);
				let paginationHTML = '';

				if (currentPage > 1) {
					paginationHTML += `<a href="#" class="prev page-numbers">PREV</a>`;
				}

				let lastPage = 0;
				pages.forEach(page => {
					if (page - lastPage > 1) {
						paginationHTML += '<span class="dots">...</span>';
					}

					if (page === currentPage) {
						paginationHTML += `<span class="current" data-page="${page}">${page}</span>`;
					} else {
						paginationHTML += `<a href="#" data-page="${page}" class="inactive">${page}</a>`;
					}

					lastPage = page;
				});
				paginationElement.innerHTML = paginationHTML;
			}

			function resetCategoryFilter() {
				currentFilter = '*';

				doc.querySelectorAll('.tpgb-category-list.active').forEach(function (element) {
					element.classList.remove('active');
				});

				const allElement = doc.querySelector('.tpgb-category-list[data-filter="*"]');
				if (allElement) {
					allElement.classList.add('active');
				}
			}

			function loadContent(page, filter = '*') {
				const tempContainer = doc.createElement('div');
				tempContainer.style.display = 'none';
				container.parentNode.appendChild(tempContainer);

				const data = new URLSearchParams({
					action: "tpgb_post_load",
					pages: page.toString(),
					dyOpt: dyoptOptValue,
					option: postOptValue,
					filter: filter
				});

				fetch(tpgb_config.ajax_url, {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded",
					},
					body: data
				})
				.then(response => response.text())
				.then(content => {
					if (content && content.trim().length > 0) {
						if (dyoptOptValue1.layout === "grid" || dyoptOptValue1.layout === "masonry"|| dyoptOptValue1.layout === "metro") {
							tempContainer.innerHTML = content;

							const existingItems = container.querySelectorAll(".grid-item");
							existingItems.forEach((item, index) => {
								setTimeout(() => {
									if (isopag) {
									isopag.remove(item);
									}
								}, index * 50);
							});

							setTimeout(() => {
								container.innerHTML = '';

								const newItems = tempContainer.querySelectorAll('.grid-item');
								newItems.forEach(item => {
									container.appendChild(item);
								});

								tempContainer.remove();
								resetCategoryFilter();
								initializeIsotope(container);
								updateCategoryCounts(listDiv);
								initializeAdditionalFeatures(listDiv, dyoptOptValue1);

								const totalPages = parseInt(paginationElement.getAttribute("data-total-page") || "1");
								updatePaginationUI(page, totalPages);
							}, existingItems.length * 50 + 100);
						} else {
							container.innerHTML = content;
							resetCategoryFilter();
							const totalPages = parseInt(paginationElement.getAttribute("data-total-page") || "1");
							updatePaginationUI(page, totalPages);
						}
					}
				})
				.catch(error => {
					console.error("Error in fetch:", error);
				});
			}

			function initializeCategoryFilters() {
				const categoryElements = doc.querySelectorAll('.tpgb-category-list');
				categoryElements.forEach(function (categoryElement) {
				categoryElement.addEventListener('click', function (event) {
					event.preventDefault();
					const filterValue = this.getAttribute('data-filter');
					currentFilter = filterValue;

					doc.querySelectorAll('.tpgb-category-list.active').forEach(function (element) {
						element.classList.remove('active');
					});

					this.classList.add('active');

					if (isopag) {
						isopag.arrange({
							filter: filterValue,
							transitionDuration: '0.4s'
						});

						setTimeout(() => {
							isopag.layout();
						}, 50);
					}
				});
				});

				const activeFilter = doc.querySelector('.tpgb-category-list.active');
				if (activeFilter) {
					currentFilter = activeFilter.getAttribute('data-filter') || '*';
				} else {
					const allElement = doc.querySelector('.tpgb-category-list[data-filter="*"]');
					if (allElement) {
						allElement.classList.add('active');
						currentFilter = '*';
					}
				}
			}

			function updateCategoryCounts(listDiv) {
				if (listDiv.querySelectorAll(".tpgb-category-filter").length > 0) {
					listDiv.querySelectorAll(".tpgb-filter-data .tpgb-categories > .tpgb-filter-list > a").forEach(function (element) {
						const filter = element.dataset.filter;
						let total_count = 0;

						if (filter !== undefined && filter !== "") {
							if (filter === "*") {
								total_count = listDiv.querySelectorAll(".post-loop-inner .grid-item").length;
							} else {
								total_count = listDiv.querySelectorAll(".post-loop-inner .grid-item" + filter).length;
							}
						}

						const countElement = element.querySelector(".tpgb-category-count");
						if (total_count > 0 && countElement) {
							countElement.innerHTML = total_count;
						}
					});
				}
			}

			function initializeAdditionalFeatures(listDiv, dyoptOptValue1) {
				if (listDiv.classList.contains("tpgb-metro") && typeof tppoMetro === "function") {
					tppoMetro(doc);
				}

				if (typeof tppostList === "function") {
					tppostList(doc);
				}

				if (listDiv.classList.contains("tpgb-equal-height") && typeof equalHeightFun === "function") {

                    equalHeightFun(listDiv);
                    setTimeout(function () {
                        isopag.layout();
                    }, 100);  
				}

				if (listDiv.querySelectorAll(".tpgb-messagebox").length > 0 && typeof tpmsgBox === "function") {
					tpmsgBox(listDiv);
				}

				if (listDiv.querySelectorAll(".tpgb-fancy-popup").length > 0) {
					listDiv.querySelectorAll(".tpgb-fancy-popup").forEach((ele) => {
						if (typeof tpgb_fancy_popup === "function") {
							tpgb_fancy_popup(ele);
						}
					});
				}

				if (listDiv.querySelectorAll(".tpgb-accordion").length > 0 && typeof accordionJS === "function") {
					accordionJS(listDiv);
				}

				if (listDiv.querySelectorAll(".tpgb-heading-animation").length > 0 && typeof tpheAnim === "function") {
					tpheAnim(listDiv);
				}
			}

			const listDiv = doc.getElementById(dyoptOptValue1.load_class);
			const container = listDiv.querySelector(".post-loop-inner");

			initializeIsotope(container);
			initializeCategoryFilters();

			const totalPages = parseInt(paginationElement.getAttribute("data-total-page") || "1");
			currentPage = parseInt(paginationElement.querySelector("span.current")?.getAttribute("data-page") || "1");
			updatePaginationUI(currentPage, totalPages);

			paginationElement.addEventListener("click", (event) => {
				const clickedItem = event.target.closest(
				"a[data-page], a.prev.page-numbers, a.next.page-numbers"
				);
				if (!clickedItem) return;

				event.preventDefault();

				const totalPages = parseInt(paginationElement.getAttribute("data-total-page") || "1");

				if (clickedItem.classList.contains("prev")) {
					currentPage = Math.max(1, currentPage - 1);
				} else if (clickedItem.classList.contains("next")) {
					currentPage = Math.min(totalPages, currentPage + 1);
				} else {
					currentPage = parseInt(clickedItem.getAttribute("data-page"));
				}

				loadContent(currentPage, currentFilter);
			});
		}
	}
}