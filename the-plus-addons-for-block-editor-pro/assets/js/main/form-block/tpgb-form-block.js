document.addEventListener("DOMContentLoaded", function () {
	const formBlocks = document.querySelectorAll(".tp-form-block");
	if (formBlocks.length === 0) {
		return;
	}
	formBlocks.forEach((formBlock) => {
		let countryCode;
		let captchaValue;
		let securityValue;
		let formDataAttributes = {};
		let recaptchaInstance = null;
		let recaptchaToken = null;
		let isSubmitting = false;
		let cloudflareWidget = null;
		const formId = formBlock.getAttribute("data-block-id");
		if (formId) {
			const form = formBlock.querySelector(".nxt-form");
			formDataAttributes = form.getAttribute("data-formdata") || "";
			const countryDropdown = formBlock.querySelector(
				".nxt-country-dropdown"
			);
			if (countryDropdown) {
				countryCode = countryDropdown.value;

				countryDropdown.addEventListener("change", function () {
					countryCode = this.value;
				});
			}

			const inputs = formBlock.querySelectorAll(
				".nxt-time-richtext, .nxt-date-richtext"
			);
			inputs.forEach((input) => {
				const originalType = input.classList.contains(
					"nxt-time-richtext"
				)
					? "time"
					: "date";
				input.addEventListener("click", (e) => {
					e.target.type = originalType;
				});
				input.addEventListener("blur", (e) => {
					e.target.type = "text";
				});
			});

			const actionOption =
				form.hasAttribute("data-actionoption") &&
				form.getAttribute("data-actionoption").trim() !== ""
					? JSON.parse(form.getAttribute("data-actionoption")).map(
							(item) => item.value
					  )
					: "";
			const verKey = form.getAttribute("data-cap");

			let redirect = "";
			let linkBlnk;
			let lnkNoFlw;

			if (actionOption && Array.isArray(actionOption)) {
				const hasRedirectOption = actionOption.some(
					(option) => option.toLowerCase() === "redirect"
				);

				if (hasRedirectOption && form.hasAttribute("data-redirect")) {
					redirect = form.getAttribute("data-redirect");
				}
				if (hasRedirectOption && form.hasAttribute("data-link-blank")) {
					linkBlnk = form.getAttribute("data-link-blank");
				}
				if (
					hasRedirectOption &&
					form.hasAttribute("data-link-nofollow")
				) {
					lnkNoFlw = form.getAttribute("data-link-nofollow");
				}
			}

			// inputs.forEach((input) => {
			// 	const originalType = input.classList.contains(
			// 		"nxt-time-richtext"
			// 	)
			// 		? "time"
			// 		: "date";
			// 	input.type = originalType;
			// 	input.addEventListener("click", (e) => {
			// 		e.target.type = originalType;
			// 	});
			// });

			const blockElement = formBlock.querySelector(".nxt-secured-option");
			if (blockElement) {
				const rawSecurityData =
					blockElement.getAttribute("data-security");
				const securityData = JSON.parse(rawSecurityData) || {};
				securityValue = securityData.securityOptions;
				captchaValue = securityData.captchaOptions;
			}

			const validateEmail = (emailInput, errorShow) => {
				if (
					!emailInput ||
					!emailInput.hasAttribute("data-required") ||
					emailInput.getAttribute("data-required") === "false"
				) {
					return true;
				}
				const emailValue = emailInput.value.trim();
				const customErrorMessage =
					errorShow.getAttribute("data-error-message");
				if (!emailValue) {
					errorShow.textContent =
						customErrorMessage || "This field is required.";
					return false;
				}

				const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				if (!emailRegex.test(emailValue)) {
					errorShow.textContent =
						customErrorMessage ||
						"Please enter a valid email address.";
					return false;
				}

				errorShow.textContent = "";
				return true;
			};

			const validateMessage = (messageTextarea, errorShow) => {
				if (
					!messageTextarea ||
					!messageTextarea.hasAttribute("data-required") ||
					messageTextarea.getAttribute("data-required") === "false"
				) {
					return true;
				}
				const messageValue = messageTextarea.value.trim();
				const customErrorMessage =
					errorShow.getAttribute("data-error-message");
				if (!messageValue) {
					errorShow.textContent =
						customErrorMessage || "This field is required.";
					return false;
				}
				errorShow.textContent = "";
				return true;
			};

			const validateCheckbox = (input, errorShow) => {
				if (
					!input ||
					!input.hasAttribute("data-required") ||
					input.getAttribute("data-required") === "false"
				) {
					return true;
				}

				let isChecked = false;

				if (input.type === "radio") {
					const radios = formBlock.querySelectorAll(
						`input[type="radio"][name="${input.name}"]`
					);
					isChecked = Array.from(radios).some(
						(radio) => radio.checked
					);
				} else {
					isChecked = input.checked;
				}

				let customErrorMessage = "This field is required.";
				if (errorShow && typeof errorShow.getAttribute === "function") {
					const msg = errorShow.getAttribute("data-error-message");
					if (msg) {
						customErrorMessage = msg;
					}
				}

				if (!isChecked) {
					if (errorShow) {
						errorShow.textContent = customErrorMessage;
					}
					return false;
				}

				if (errorShow) {
					errorShow.textContent = "";
				}
				return true;
			};

			const validateUrl = (url, errorShow) => {
				if (
					!url ||
					!url.hasAttribute("data-required") ||
					url.getAttribute("data-required") === "false"
				) {
					return true;
				}
				const messageValue = isValidURL(url.value.trim());
				const customErrorMessage =
					errorShow.getAttribute("data-error-message");
				if (!messageValue) {
					errorShow.textContent =
						customErrorMessage || "This field is required.";
					return false;
				}
				errorShow.textContent = "";
				return true;
			};
			const validateTime = (time, errorShow) => {
				if (
					!time ||
					!time.hasAttribute("data-required") ||
					time.getAttribute("data-required") === "false"
				) {
					return true;
				}

				const regex = /^(0?[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/;
				const isValid = regex.test(time.value.trim());

				const customErrorMessage =
					errorShow?.getAttribute("data-error-message");

				if (!isValid) {
					errorShow.textContent =
						customErrorMessage || "This field is required.";
					return false;
				}

				errorShow.textContent = "";
				return true;
			};

			const validateDate = (dateInput, errorShow) => {
				if (
					!dateInput ||
					!dateInput.hasAttribute("data-required") ||
					dateInput.getAttribute("data-required") === "false"
				) {
					return true;
				}

				const regex =
					/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
				const isValid = regex.test(dateInput.value.trim());

				const customErrorMessage =
					errorShow?.getAttribute("data-error-message");

				if (!isValid) {
					errorShow.textContent =
						customErrorMessage ||
						"Invalid date format. Use YYYY-MM-DD.";
					return false;
				}

				errorShow.textContent = "";
				return true;
			};

			const validatePhone = (numberInput, errorShow) => {
				if (!numberInput) {
					errorShow.textContent = "";
					return true;
				}

				const pattern = numberInput.getAttribute("data-pattern");
				const numberValue = numberInput.value.trim();
				const customErrorMessage =
					errorShow.getAttribute("data-error-message");

				if (
					numberInput.hasAttribute("data-required") &&
					numberInput.getAttribute("data-required") !== "false" &&
					!numberValue
				) {
					errorShow.textContent =
						customErrorMessage || "This field is required.";
					return false;
				}

				if (numberValue && pattern && pattern !== "none") {
					const regex = new RegExp(pattern);
					if (!regex.test(numberValue)) {
						errorShow.textContent =
							customErrorMessage ||
							"Please enter a valid number.";
						return false;
					}
				}
				if (numberValue && pattern && pattern === "none") {
					const regex = /^[+]?[0-9\s\-()]+$/;
					// allows: digits, +, space, -, ( )
					if (!regex.test(numberValue)) {
						errorShow.textContent =
							customErrorMessage ||
							"Please enter a valid phone number.";
						return false;
					}
				}

				errorShow.textContent = "";
				return true;
			};

			const nameInputs = formBlock.querySelectorAll(".nxt-name-richtext");
			const emailInputs = formBlock.querySelectorAll(
				".nxt-email-richtext"
			);
			const messageTextareas = formBlock.querySelectorAll(
				".nxt-message-richtext"
			);
			const checkboxGroups = formBlock.querySelectorAll(
				".tp-form-checkbox-button"
			);

			const radioGroups = formBlock.querySelectorAll(
				".tp-form-radio-button "
			);

			const selectFields =
				formBlock.querySelectorAll(".nxt-option-field");
			const urlInputs = formBlock.querySelectorAll(".nxt-url-richtext");
			const numberInputs = formBlock.querySelectorAll(
				".nxt-number-richtext"
			);
			const timeInputs = formBlock.querySelectorAll(".nxt-time-richtext");
			const dateInputs = formBlock.querySelectorAll(".nxt-date-richtext");
			const phoneInputs = formBlock.querySelectorAll(
				".nxt-phone-richtext"
			);

			// Select corresponding error spans
			const errorShows = formBlock.querySelectorAll(".nxt-error-show");
			const errorShowsEmail =
				formBlock.querySelectorAll(".nxt-error-email");
			const errorShowsMessage =
				formBlock.querySelectorAll(".nxt-message-error");
			const errorShowsCheckbox = formBlock.querySelectorAll(
				".nxt-error-checkbox"
			);
			const errorShowsRadio = formBlock.querySelectorAll(
				".nxt-error-radiobox"
			);
			const errorShowsSelect =
				formBlock.querySelectorAll(".nxt-error-select");
			const errorShowsNumber =
				formBlock.querySelectorAll(".nxt-error-number");
			const errorShowsUrl = formBlock.querySelectorAll(".nxt-error-url");
			const errorShowsTime =
				formBlock.querySelectorAll(".nxt-error-time");
			const errorShowsDate =
				formBlock.querySelectorAll(".nxt-error-date");

			const errorShowsPhone =
				formBlock.querySelectorAll(".nxt-error-phone");

			nameInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateMessage(input, errorShows[index])
				);
			});

			emailInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateEmail(input, errorShowsEmail[index])
				);
			});

			messageTextareas.forEach((textarea, index) => {
				textarea.addEventListener("input", () =>
					validateMessage(textarea, errorShowsMessage[index])
				);
			});

			checkboxGroups.forEach((group) => {
				const checkboxes = group.querySelectorAll(
					'input[type="checkbox"]'
				);
				const errorElement = group.querySelector(".nxt-error-checkbox");
				checkboxes.forEach((checkbox) => {
					checkbox.addEventListener("change", () =>
						validateCheckbox(checkbox, errorElement)
					);
				});
			});

			radioGroups.forEach((group) => {
				const radio = group.querySelectorAll('input[type="radio"]');
				const errorElement = group.querySelector(".nxt-error-radiobox");
				radio.forEach((radio) => {
					radio.addEventListener("change", () =>
						validateCheckbox(radio, errorElement)
					);
				});
			});

			selectFields.forEach((input, index) => {
				input.addEventListener("change", () =>
					validateMessage(input, errorShowsSelect[index])
				);
			});

			numberInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateMessage(input, errorShowsNumber[index])
				);
			});

			urlInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateUrl(input, errorShowsUrl[index])
				);
			});

			timeInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateTime(input, errorShowsUrl[index])
				);
			});

			dateInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validateDate(input, errorShowsDate[index])
				);
			});

			phoneInputs.forEach((input, index) => {
				input.addEventListener("input", () =>
					validatePhone(input, errorShowsPhone[index])
				);
			});

			const isFormValid = () => {
				let isValid = true;
				const validateFields = (inputs, validator, errorElements) => {
					inputs.forEach((input, index) => {
						if (input.hasAttribute("data-required")) {
							const isFieldValid = validator(
								input,
								errorElements[index]
							);
							isValid = isValid && isFieldValid;
						}
					});
				};

				validateFields(nameInputs, validateMessage, errorShows);
				validateFields(emailInputs, validateEmail, errorShowsEmail);
				validateFields(
					messageTextareas,
					validateMessage,
					errorShowsMessage
				);
				validateFields(selectFields, validateMessage, errorShowsSelect);
				validateFields(numberInputs, validateMessage, errorShowsNumber);
				validateFields(urlInputs, validateMessage, errorShowsUrl);
				validateFields(timeInputs, validateTime, errorShowsTime);
				validateFields(dateInputs, validateDate, errorShowsDate);
				validateFields(phoneInputs, validatePhone, errorShowsPhone);
				//handling radio and checkbox manually
				checkboxGroups.forEach((group) => {
					const firstCheckbox = group.querySelector(
						'input[type="checkbox"]'
					);
					const errorElement = group.querySelector(
						".nxt-error-checkbox"
					);

					if (
						firstCheckbox &&
						firstCheckbox.hasAttribute("data-required")
					) {
						const isCheckboxValid = validateCheckbox(
							firstCheckbox,
							errorElement
						);
						isValid = isValid && isCheckboxValid;
					}
				});

				radioGroups.forEach((group) => {
					const firstRadio = group.querySelector(
						'input[type="radio"]'
					);
					const errorElement = group.querySelector(
						".nxt-error-radiobox"
					);

					if (
						firstRadio &&
						firstRadio.hasAttribute("data-required")
					) {
						const isRadioValid = validateCheckbox(
							firstRadio,
							errorElement
						);
						isValid = isValid && isRadioValid;
					}
				});

				return isValid;
			};

			if (securityValue === "honeypot") {
				const fields = form.querySelectorAll(
					"input:not([type='submit']), textarea, select"
				);

				fields.forEach((field, index) => {
					const honeypotWrapper = document.createElement("div");
					honeypotWrapper.style.display = "none";

					const honeypot = document.createElement("input");
					honeypot.type = "text";
					honeypot.name = `honeypot_${index + 1}`;
					honeypot.className = "nxt-honeypot-field";

					honeypotWrapper.appendChild(honeypot);

					field.insertAdjacentElement("afterend", honeypotWrapper);
				});
			}

			const checkbox = formBlock.querySelector(".nxt-accept-richtext");
			const submitButton = formBlock.querySelector(".nxt-submit-button");

			if (checkbox) {
				if (checkbox.required) {
					submitButton.disabled = !checkbox.checked;
				} else {
					submitButton.disabled = false;
				}

				checkbox.addEventListener("change", () => {
					if (checkbox.required) {
						submitButton.disabled = !checkbox.checked;
					} else {
						submitButton.disabled = false;
					}
				});
			}
			const formElement = formBlock.querySelector(".nxt-form");

			const loadCloudflareScript = () => {
				const script = document.createElement("script");
				script.src =
					"https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit";
				script.async = true;
				script.defer = true;
				formBlock.appendChild(script);
				script.onload = () => {};
			};
			if (formElement) {
				formElement.addEventListener("submit", handleSubmit);

				const loadRecaptchaScript = (captchaopt) => {
					const script = document.createElement("script");
					script.src =
						captchaopt === "v3"
							? `https://www.google.com/recaptcha/api.js?render=${verKey}`
							: "https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoadCallback&render=explicit";
					script.async = true;
					script.defer = true;
					formBlock.appendChild(script);
					script.onload = () => {
						if (captchaopt === "v3") {
							grecaptcha.ready(() => {});
						}
					};
				};

				securityValue === "recaptcha"
					? loadRecaptchaScript(captchaValue)
					: securityValue === "cloudflare"
					? loadCloudflareScript()
					: null;
			}

			let cloudSiteKey =
				blockElement && blockElement.hasAttribute("data-cloud")
					? blockElement.getAttribute("data-cloud")
					: "";

			const renderCloudflareTurnstile = () => {
				const container = formBlock.querySelector(
					"#recaptcha-container"
				);
				if (container) {
					cloudflareWidget = turnstile.render(container, {
						sitekey: cloudSiteKey,
						callback: onCloudflareVerifyCallback,
					});
				}
			};

			const onCloudflareVerifyCallback = (token) => {
				recaptchaToken = token;
				processCaptchaSuccess();
			};

			function handleSubmit(event) {
				event.preventDefault();
				if (!isFormValid()) {
					return;
				}
				if (isSubmitting) {
					return;
				}

				isSubmitting = true;

				if (securityValue === "honeypot") {
					const honeypotFields = form.querySelectorAll(
						".nxt-honeypot-field"
					);
					let honeypotTriggered = false;

					honeypotFields.forEach((field) => {
						if (field.value.trim() !== "") {
							honeypotTriggered = true;
						}
					});

					if (honeypotTriggered) {
						// Bot detected - silently fail or show generic error
						isSubmitting = false;
						showMessage(
							"Form submission failed. Please try again.",
							true
						);
						return;
					}
				}

				if (securityValue === "recaptcha") {
					if (captchaValue === "v3") {
						grecaptcha.ready(function () {
							grecaptcha
								.execute(verKey, {
									action: "submit",
								})
								.then(onRecaptchaVerifyCallback);
						});
					} else if (captchaValue === "v2i") {
						grecaptcha.execute(recaptchaInstance);
					} else if (captchaValue === "v2") {
						const response =
							grecaptcha.getResponse(recaptchaInstance);
						if (response) {
							onRecaptchaVerifyCallback(response);
						} else {
							isSubmitting = false;
							showMessage("Please complete the reCAPTCHA.", true);
						}
					} else {
						isSubmitting = false;
					}
				} else if (securityValue === "cloudflare") {
					!cloudflareWidget ? renderCloudflareTurnstile() : null;
					turnstile.reset(cloudflareWidget);
					turnstile.execute(cloudflareWidget);
				} else {
					processCaptchaSuccess();
				}
			}

			const onRecaptchaVerifyCallback = (token) => {
				recaptchaToken = token;
				processCaptchaSuccess();
			};

			const processCaptchaSuccess = () => {
				submitForm(formDataAttributes);
			};

			function submitForm(formDataAttributes) {
				const formData = new FormData();

				const getFieldValue = (field) => {
					const fieldData = [];
					const fieldType = field.type;

					let labelText = "";
					const label =
						fieldType === "checkbox"
							? field
									.closest(".tp-form-checkbox-button")
									?.querySelector(".nxt-check-title-label")
							: fieldType === "radio"
							? field
									.closest(".tp-form-radio-button")
									?.querySelector(".nxt-radio-title-label")
							: field.closest(".nxt-input-container")
									?.previousElementSibling ||
							  field
									.closest(".nxt-radio-box")
									?.querySelector("label") ||
							  field
									.closest(".nxt-time-input")
									?.querySelector(".nxt-time-label") ||
							  field
									.closest(".nxt-date-input")
									?.querySelector(".nxt-date-label") ||
							  field
									.closest(".nxt-option-container")
									?.querySelector(".nxt-option-label") ||
							  field
									.closest(".nxt-phone-input")
									?.querySelector(".nxt-phone-label") ||
							  field
									.closest(".nxt-message-input")
									?.querySelector(".nxt-message-label");

					if (label) {
						labelText = label.innerText.trim();
					} else {
						labelText = field.name;
					}

					if (
						(fieldType === "radio" || fieldType === "checkbox") &&
						!field.checked
					) {
						return fieldData;
					}

					let value = field.value || "";

					if (fieldType === "tel") {
						if (countryCode && field.value) {
							value = `${countryCode} ${field.value}`;
						}
					}

					if (value) {
						fieldData.push({ label: labelText, value: value });
					}

					return fieldData;
				};

				const fields = formBlock.querySelectorAll(
					".nxt-form input:not([type='submit']), .nxt-form select, .nxt-form textarea"
				);

				const fieldsArray = Array.from(fields);

				const processedFields = new Map();

				fieldsArray.forEach((field) => {
					if (field.type === "checkbox") {
						const fieldName = field.name;
						if (!processedFields.has(fieldName)) {
							const checkboxGroup = formBlock.querySelectorAll(
								`input[name="${fieldName}"]:checked`
							);
							const values = Array.from(checkboxGroup).map(
								(cb) => cb.value
							);
							if (values.length > 0) {
								const label =
									field
										.closest(".tp-form-checkbox-button")
										?.querySelector(
											".nxt-check-title-label"
										)
										?.innerText.trim() || fieldName;
								formData.append(label, values.join(", "));
							}
							processedFields.set(fieldName, true);
						}
					} else {
						const fieldValues = getFieldValue(field);
						fieldValues.forEach(({ label, value }) => {
							formData.append(label, value);
						});
					}
				});

				const fieldNames = [
					...new Set(fieldsArray.map((field) => field.name)),
				];
				fieldNames.forEach((fieldName) => {
					const fields = fieldsArray.filter(
						(field) => field.name === fieldName
					);
					const fieldData = fields
						.map((field) => getFieldValue(field))
						.flat();

					if (fieldData.length > 0) {
						const emailActions = ["Email", "Auto Respond Email"];
						const hasEmailAction =
							Array.isArray(actionOption) &&
							actionOption.some((option) =>
								emailActions.includes(option)
							);
						const notEmailAction =
							Array.isArray(actionOption) &&
							actionOption.some(
								(option) => !emailActions.includes(option)
							);

						// if (hasEmailAction) {
						// 	fieldData.forEach((field, index) => {
						// 		formData.append(
						// 			`${fieldName}_${index}`,
						// 			field.value
						// 		);
						// 		formData.append(
						// 			`label[${fieldName}_${index}]`,
						// 			field.label
						// 		);
						// 	});
						// }

						if (notEmailAction) {
							const hasWebhook = actionOption.some(
								(option) => option.toLowerCase() === "webhook"
							);
							if (hasWebhook) {
								const formValues = {
									checkboxes: Array.from(
										formBlock.querySelectorAll(
											".nxt-check-box"
										)
									)
										.filter(
											(group) =>
												group.id ||
												group
													.querySelector(
														".nxt-check-label"
													)
													?.textContent?.trim() ||
												group.querySelector(
													".nxt-check-richtext"
												)?.name
										)
										.map((group) => ({
											id:
												group.id ||
												group
													.querySelector(
														".nxt-check-label"
													)
													?.textContent?.trim() ||
												group.querySelector(
													".nxt-check-richtext"
												)?.name,
											value: Array.from(
												group.querySelectorAll(
													'input[type="checkbox"]:checked'
												)
											)
												.map((cb) => cb.value)
												.join(","),
										})),
									dates: Array.from(
										formBlock.querySelectorAll(
											".nxt-date-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-date-input"
														)
														?.querySelector(
															".nxt-date-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(".nxt-date-input")
													?.querySelector(
														".nxt-date-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),

									emails: Array.from(
										formBlock.querySelectorAll(
											".nxt-email-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-email-input"
														)
														?.querySelector(
															".nxt-email-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(".nxt-email-input")
													?.querySelector(
														".nxt-email-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),

									messages: Array.from(
										formBlock.querySelectorAll(
											".nxt-message-richtext"
										)
									)
										.filter(
											(textarea) =>
												textarea.value &&
												(textarea.id ||
													textarea
														.closest(
															".nxt-message-input"
														)
														?.querySelector(
															".nxt-message-label"
														)
														?.textContent?.trim() ||
													textarea.name)
										)
										.map((textarea) => ({
											id:
												textarea.id ||
												textarea
													.closest(
														".nxt-message-input"
													)
													?.querySelector(
														".nxt-message-label"
													)
													?.textContent?.trim() ||
												textarea.name,
											value: textarea.value,
										})),

									names: Array.from(
										formBlock.querySelectorAll(
											".nxt-name-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-name-input"
														)
														?.querySelector(
															".nxt-name-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(".nxt-name-input")
													?.querySelector(
														".nxt-name-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),

									numbers: Array.from(
										formBlock.querySelectorAll(
											".nxt-number-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-number-input"
														)
														?.querySelector(
															".nxt-number-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(
														".nxt-number-input"
													)
													?.querySelector(
														".nxt-number-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),

									selects: Array.from(
										formBlock.querySelectorAll(
											".nxt-option-container select"
										)
									)
										.filter(
											(select) =>
												select.value &&
												(select.closest(
													".nxt-option-container"
												).id ||
													select
														.closest(
															".nxt-option-container"
														)
														?.querySelector(
															".nxt-option-label"
														)
														?.textContent?.trim() ||
													select.name)
										)
										.map((select) => ({
											id:
												select.closest(
													".nxt-option-container"
												).id ||
												select
													.closest(
														".nxt-option-container"
													)
													?.querySelector(
														".nxt-option-label"
													)
													?.textContent?.trim() ||
												select.name,
											value: select.value,
										})),

									phones: Array.from(
										formBlock.querySelectorAll(
											".nxt-phone-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-phone-input"
														)
														?.querySelector(
															".nxt-phone-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => {
											const dropdown =
												input.previousElementSibling?.classList.contains(
													"nxt-country-dropdown"
												)
													? input.previousElementSibling
													: null;

											let country = "";
											if (
												dropdown &&
												dropdown.tagName === "SELECT"
											) {
												country =
													dropdown.options[
														dropdown.selectedIndex
													]?.value || "";
											}

											return {
												id:
													input.id ||
													input
														.closest(
															".nxt-phone-input"
														)
														?.querySelector(
															".nxt-phone-label"
														)
														?.textContent?.trim() ||
													input.name,
												value:
													(country
														? `${country} `
														: "") + input.value,
											};
										}),

									radios: Array.from(
										formBlock.querySelectorAll(
											".nxt-radio-box"
										)
									)
										.filter(
											(group) =>
												group.id ||
												group
													.closest(
														".tp-form-radio-button"
													)
													?.querySelector(
														".nxt-radio-title-label"
													)
													?.textContent?.trim() ||
												group.querySelector(
													".nxt-radio-richtext"
												)?.name
										)
										.map((group) => ({
											id:
												group.id ||
												group
													.closest(
														".tp-form-radio-button"
													)
													?.querySelector(
														".nxt-radio-title-label"
													)
													?.textContent?.trim() ||
												group.querySelector(
													".nxt-radio-richtext"
												)?.name,
											value:
												(
													group.querySelector(
														'input[type="radio"]:checked'
													) || {}
												).value || "",
										})),

									times: Array.from(
										formBlock.querySelectorAll(
											".nxt-time-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-time-input"
														)
														?.querySelector(
															".nxt-time-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(".nxt-time-input")
													?.querySelector(
														".nxt-time-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),

									urls: Array.from(
										formBlock.querySelectorAll(
											".nxt-url-richtext"
										)
									)
										.filter(
											(input) =>
												input.value &&
												(input.id ||
													input
														.closest(
															".nxt-url-input"
														)
														?.querySelector(
															".nxt-url-label"
														)
														?.textContent?.trim() ||
													input.name)
										)
										.map((input) => ({
											id:
												input.id ||
												input
													.closest(".nxt-url-input")
													?.querySelector(
														".nxt-url-label"
													)
													?.textContent?.trim() ||
												input.name,
											value: input.value,
										})),
								};

								Object.keys(formValues).forEach((group) => {
									formValues[group].forEach((field) => {
										console.log("field = ", field);

										formData.append(
											"nxt_" + field.id,
											field.value
										);
									});
								});
							}

							const allValues = fieldData
								.map((field) => field.value)
								.join(", ");
							formData.append(`${fieldName}-field`, allValues);
						}

						// if (hasEmailAction) {
						// 	const combinedValues = fieldData
						// 		.map((field) => field.value)
						// 		.join(", ");
						// 	formData.append(`${fieldName}[]`, combinedValues);
						// }
					}
				});

				const radioGroups = new Map();
				const checkedRadios = formBlock.querySelectorAll(
					'input[type="radio"]:checked'
				);

				checkedRadios.forEach((radio) => {
					const label = radio
						.closest(".nxt-radio-box")
						?.querySelector(".nxt-radio-title-label");
					const groupName = label?.innerText.trim() || "radio-group";

					if (!radioGroups.has(groupName)) {
						radioGroups.set(groupName, []);
					}
					radioGroups.get(groupName).push(radio.value);
				});

				radioGroups.forEach((values, labelText) => {
					const combinedValues = values.join(", ");
					formData.append(labelText, combinedValues);
				});

				formData.append("actionOption", formDataAttributes || []);
				formData.append("action", "nxt_form_action");
				formData.append("nonce", tpgb_config.tpgb_nonce);

				if (
					securityValue === "recaptcha" ||
					securityValue === "cloudflare"
				) {
					formData.append("captchaopt", captchaValue);
					formData.append("securityValue", securityValue);
					formData.append(
						"g-recaptcha-response",
						recaptchaToken || ""
					);
				} else {
					formData.append("captchaopt", "none");
				}
				const emailFields = [
					{
						to: "emailto1",
						subject: "subject1",
						message1: "message1",
					},
					{ subject: "subject2", message1: "message2" },
				];

				emailFields.forEach((field, index) => {
					const emailTo = formDataAttributes[`emailTo${index + 1}`];
					const subject = formDataAttributes[`subject${index + 1}`];
					const message = formDataAttributes[`message${index + 1}`];

					emailTo ? formData.append(field.to, emailTo) : null;
					subject ? formData.append(field.subject, subject) : null;
					message ? formData.append(field.message1, message) : null;
				});
				if (!actionOption || actionOption.length === 0) {
					showMessage("No form action is selected.", true);
					isSubmitting = false;
					return;
				}
				if (isFormValid()) {
					if (
						actionOption &&
						Array.isArray(actionOption) &&
						!(
							actionOption.length === 1 &&
							actionOption[0].toLowerCase() === "redirect"
						)
					) {
						const submitButton =
							formBlock.querySelector(".nxt-submit");
						const loaderSpan =
							submitButton.querySelector(".nxt-loader");
						const buttonSvg =
							submitButton.querySelector(".nxt-button-svg");
						let buttonText =
							submitButton.querySelector(".nxt-btn-text");
						if (buttonText) buttonText.style.opacity = "0";
						if (buttonSvg) buttonSvg.style.opacity = "0";
						loaderSpan.style.opacity = "1";

						fetch(tpgb_config.ajax_url, {
							method: "POST",
							body: formData,
							credentials: "same-origin",
							headers: {
								"X-WP-Nonce": tpgb_config.tpgb_nonce,
							},
						})
							.then((response) => response.json())
							.then((responseData) => {
								isSubmitting = false;
								loaderSpan.style.opacity = "0";
								buttonSvg
									? (buttonSvg.style.opacity = "1")
									: "";
								buttonText
									? (buttonText.style.opacity = "1")
									: "";
								if (responseData.success) {
									const successMessage =
										formBlock
											.querySelector(
												".nxt-success-message"
											)
											.getAttribute(
												"data-success-message"
											) ||
										"Your submission was successful.";

									showMessage(successMessage, false);
									// setTimeout(() => {
									//     window.location.reload();
									// }, 1000);
									const formFields =
										formBlock.querySelectorAll(
											"input, textarea, select"
										);
									formFields.forEach((field) => {
										if (
											field.type === "checkbox" ||
											field.type === "radio"
										) {
											field.checked = false;
										} else {
											field.value = "";
										}
									});
								} else {
									const errorMessage =
										responseData.data ||
										"Unknown error occurred.";

									showMessage(errorMessage, true);
									loaderSpan.style.opacity = "0";
									buttonSvg
										? (buttonSvg.style.opacity = "1")
										: "";
									buttonText
										? (buttonText.style.opacity = "1")
										: "";
								}
							})
							.catch((error) => {
								loaderSpan.style.opacity = "0";
								buttonSvg
									? (buttonSvg.style.opacity = "1")
									: "";
								buttonText
									? (buttonText.style.opacity = "1")
									: "";
								showMessage(
									"An error occurred while sending the form data.",
									true
								);
								isSubmitting = false;
							});
					}
					setTimeout(() => {
						if (redirect && isValidURL(redirect)) {
							if (linkBlnk === "1") {
								let newWindow = window.open("", "_blank");

								if (lnkNoFlw === "1") {
									let linkElement =
										newWindow.document.createElement("a");
									linkElement.setAttribute("href", redirect);
									linkElement.setAttribute("rel", "nofollow");
									newWindow.document.body.appendChild(
										linkElement
									);

									linkElement.click();
								} else {
									newWindow.location.href = redirect;
								}
							} else {
								window.location.href = redirect;
							}
						}
					}, 2000);
				}
			}

			const isValidURL = (string) => {
				try {
					new URL(string);
					return true;
				} catch (_) {
					return false;
				}
			};

			const showMessage = (message, isError) => {
				const messageElement = formBlock.querySelector(
					".nxt-success-message"
				);
				if (messageElement) {
					messageElement.textContent = message;
					messageElement.classList.remove(
						"nxt-success-message",
						"nxt-error-message"
					);
					messageElement.classList.add(
						isError ? "nxt-error-message" : "nxt-success-message"
					);
					messageElement.style.display = "block";
				}
			};

			window.onRecaptchaLoadCallback = function () {
				const recaptchaContainer = formBlock.querySelector(
					"#recaptcha-container"
				);

				if (!recaptchaContainer) {
					return;
				}

				if (captchaValue === "v2i") {
					const container = formBlock.querySelector(
						"#recaptcha-container"
					);
					if (container) {
						recaptchaInstance = grecaptcha.render(
							"recaptcha-container",
							{
								sitekey: verKey,
								size: "invisible",
								callback: onRecaptchaVerifyCallback,
							}
						);
					}
				} else if (captchaValue === "v2") {
					const container = formBlock.querySelector(
						"#recaptcha-container"
					);
					if (container) {
						recaptchaInstance = grecaptcha.render(
							"recaptcha-container",
							{
								sitekey: verKey,
								size: "normal",
							}
						);
					}
				}
			};
		}
	});
});