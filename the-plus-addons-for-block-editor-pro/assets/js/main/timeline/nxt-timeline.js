// Timeline Progress Bar Script - Condensed
function initTimelineProgress() {
	document.querySelectorAll(".tpgb-timeline-list").forEach(block => {
		const container = block.querySelector(".post-loop-inner");
		const trackDraw = block.querySelector(".timeline-track-draw");
		const items = block.querySelectorAll(".timeline-item-wrap");
		const tooltips = block.querySelectorAll(".timeline-tooltip-wrap");
		const track = block.querySelector(".timeline-track");

		if (!container || !trackDraw || !items.length) return;

		const animColor = block.getAttribute("data-anim-color") || 
						 container?.getAttribute("data-anim-color") || "";

		// Setup trackDraw
		Object.assign(trackDraw.style, {
			backgroundColor: animColor, height: "0%", transition: "height 0.3s ease",
			position: "absolute", display: "block", top: "0", left: "50%",
			width: track.style.width, marginLeft: "-.5px", zIndex: "2"
		});

		// Setup tooltips
		tooltips.forEach(wrap => wrap.style.transition = "background-color 0.3s ease");

		// Position icons
		const trackWidth = track.offsetWidth;
		const leftOffset = `calc(100% + ${trackWidth / 2}px)`;
		const rightOffset = `calc(100% - ${trackWidth / 2}px)`;

		block.querySelectorAll(".point-icon").forEach(icon => {
			const sibling = icon.previousElementSibling || icon.nextElementSibling;
			if (sibling?.classList.contains("text-right")) {
				icon.style.left = leftOffset; icon.style.right = "";
			} else if (sibling?.classList.contains("text-left")) {
				icon.style.right = rightOffset; icon.style.left = "";
			}
		});

		// Position beginning/end icons
		const calcPercent = (width, ref, mult, offset) => 
			Math.abs(width - ref) < 0.1 ? offset : ((width - 1) / (ref - 1)) * mult - 50;

		block.querySelectorAll(".tpgb-beginning-icon").forEach(icon => 
			icon.style.transform = `translateX(${Math.abs(trackWidth - 15) < 0.1 ? 5 : 
				Math.abs(trackWidth - 1) < 0.1 ? -50 : calcPercent(trackWidth, 14, 55, -50)}%)`
		);

		block.querySelectorAll(".timeline-end-icon").forEach(icon => 
			icon.style.transform = `translateX(${Math.abs(trackWidth - 20) < 0.1 ? 1.6429 : 
				Math.abs(trackWidth - 1) < 0.1 ? -50 : calcPercent(trackWidth, 19, 51.6429, -50)}%)`
		);

		// Progress update
		block.updateProgress = () => {
			const rect = container.getBoundingClientRect();
			const progress = rect.top <= window.innerHeight / 2 ? 
				rect.bottom > window.innerHeight / 2 ? 
					Math.min(100, Math.max(0, ((window.innerHeight / 2 - rect.top) / container.offsetHeight) * 100)) : 100 : 0;

			trackDraw.style.height = progress + "%";
			if (progress > 0) trackDraw.style.backgroundColor = animColor;

			items.forEach((item, idx) => {
				const itemPos = ((item.offsetTop - container.offsetTop) / container.offsetHeight) * 100;
				const tooltip = tooltips[idx];
				if (tooltip) tooltip.style.backgroundColor = progress > 0 && progress >= itemPos ? animColor : "";
			});
		};

		block.updateProgress();
	});

	// Event handlers
	let ticking = false;
	const handleScroll = () => {
		if (!ticking) {
			requestAnimationFrame(() => {
				document.querySelectorAll(".tpgb-timeline-list").forEach(block => block.updateProgress?.());
				ticking = false;
			});
			ticking = true;
		}
	};

	window.addEventListener("scroll", handleScroll, { passive: true });
	window.addEventListener("resize", () => 
		document.querySelectorAll(".tpgb-timeline-list").forEach(block => block.updateProgress?.())
	);
}

// Initialize
document.readyState === "loading" ? 
	document.addEventListener("DOMContentLoaded", initTimelineProgress) : initTimelineProgress();