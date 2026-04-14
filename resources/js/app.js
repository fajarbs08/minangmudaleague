/**
* Theme: Velok- Responsive Bootstrap 5 Admin Dashboard
* Author: FoxPixel
* Module/App: Main Js
*/

import $ from 'jquery'

window.jQuery = window.$ = $

import bootstrap from 'bootstrap/dist/js/bootstrap.min.js'
window.bootstrap = bootstrap;

import 'simplebar'
import 'iconify-icon'
import { createIcons, icons } from "lucide";

import Inputmask from 'inputmask';
import Choices from 'choices.js';

// Components
class Components {

  initBootstrapComponents() {

    // Popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // offcanvas
    const offcanvasElementList = document.querySelectorAll('.offcanvas')
    const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new bootstrap.Offcanvas(offcanvasEl))

    //Toasts
    var toastPlacement = document.getElementById("toastPlacement");
    if (toastPlacement) {
      document.getElementById("selectToastPlacement").addEventListener("change", function () {
        if (!toastPlacement.dataset.originalClass) {
          toastPlacement.dataset.originalClass = toastPlacement.className;
        }
        toastPlacement.className = toastPlacement.dataset.originalClass + " " + this.value;
      });
    }

    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    var toastList = toastElList.map(function (toastEl) {
      return new bootstrap.Toast(toastEl)
    })


    const alertTrigger = document.getElementById('liveAlertBtn')
    if (alertTrigger) {
      alertTrigger.addEventListener('click', () => {
        alert('Berhasil! Anda memicu pesan alert ini.', 'success')
      })
    }

  }

  initfullScreenListener() {
    var fullScreenBtn = document.querySelector('[data-toggle="fullscreen"]');

    if (fullScreenBtn) {
      fullScreenBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.body.classList.toggle('fullscreen-enable')
        if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {
          // current working methods
          if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
          } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
          } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
          }
        } else {
          if (document.cancelFullScreen) {
            document.cancelFullScreen();
          } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
          }
        }
      });
    }
  }

  // Counter Number
  initCounter() {
    var counter = document.querySelectorAll(".counter-value");
    var speed = 250; // The lower the slower
    counter &&
      counter.forEach(function (counter_value) {
        function updateCount() {
          var target = +counter_value.getAttribute("data-target");
          var count = +counter_value.innerText;
          var inc = target / speed;
          if (inc < 1) {
            inc = 1;
          }
          // Check if target is reached
          if (count < target) {
            // Add inc to count and output in counter_value
            counter_value.innerText = (count + inc).toFixed(0);
            // Call function every ms
            setTimeout(updateCount, 1);
          } else {
            counter_value.innerText = numberWithCommas(target);
          }
          numberWithCommas(counter_value.innerText);
        }
        updateCount();
      });

    function numberWithCommas(x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
  }

  init() {
    this.initBootstrapComponents();
    this.initfullScreenListener();
    this.initCounter();
  }
}

// Delete modal binding (uses data-delete-form and data-delete-name on the modal)
const initDeleteModals = () => {
  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-bs-target][data-action]')
    if (!trigger || trigger.disabled) return

    const target = trigger.getAttribute('data-bs-target')
    if (!target || !target.startsWith('#')) return

    const modalEl = document.querySelector(target)
    if (!modalEl) return

    const formSelector = modalEl.getAttribute('data-delete-form')
    const nameSelector = modalEl.getAttribute('data-delete-name')
    const formEl = formSelector ? modalEl.querySelector(formSelector) : null
    const nameEl = nameSelector ? modalEl.querySelector(nameSelector) : null

    if (formEl) formEl.setAttribute('action', trigger.dataset.action || '')
    if (nameEl) nameEl.textContent = trigger.dataset.name || '-'
  })
}

const initTableDropdownOverflow = () => {
  const toggleOverflow = (event, isOpen) => {
    const dropdown = event.target.closest('.dropdown, .dropup')
    if (!dropdown) return

    const tableWrap = dropdown.closest('.competition-table-wrap')
    if (!tableWrap) return

    if (isOpen) {
      tableWrap.classList.add('dropdown-open')
      return
    }

    const stillOpen = tableWrap.querySelector('.dropdown.show, .dropup.show')
    if (!stillOpen) {
      tableWrap.classList.remove('dropdown-open')
    }
  }

  document.addEventListener('show.bs.dropdown', (event) => toggleOverflow(event, true))
  document.addEventListener('hidden.bs.dropdown', (event) => toggleOverflow(event, false))
}

const initActionDropdowns = () => {
  document.querySelectorAll('.competition-action-toggle').forEach((button) => {
    button.setAttribute('data-bs-display', 'static')
    button.setAttribute('data-bs-offset', '0,4')
  })
}

const initSearchAutocomplete = () => {
  const forms = document.querySelectorAll('[data-search-form]')

  forms.forEach((form) => {
    const input = form.querySelector('[data-search-autocomplete]')
    if (!input) return

    const suggestUrl = input.dataset.searchSuggestUrl
    if (!suggestUrl) return

    const luckyLink = form.querySelector('[data-search-lucky]')
    const wrapper = input.parentElement
    if (!wrapper) return

    const dropdown = document.createElement('div')
    dropdown.className = 'list-group position-absolute start-0 end-0 mt-2 shadow-sm d-none'
    dropdown.style.zIndex = '1080'
    wrapper.appendChild(dropdown)

    let activeIndex = -1
    let abortController = null
    let debounceTimer = null

    const setLuckyLink = (lucky) => {
      if (!luckyLink) return

      if (!lucky?.url) {
        luckyLink.classList.add('d-none')
        luckyLink.removeAttribute('href')
        return
      }

      luckyLink.href = lucky.url
      luckyLink.textContent = `Hasil Teratas: ${lucky.label}`
      luckyLink.classList.remove('d-none')
    }

    const hideDropdown = () => {
      dropdown.classList.add('d-none')
      dropdown.innerHTML = ''
      activeIndex = -1
    }

    const suggestionItems = () => [...dropdown.querySelectorAll('[data-search-item]')]

    const updateActiveItem = () => {
      suggestionItems().forEach((item, index) => {
        item.classList.toggle('active', index === activeIndex)
      })
    }

    const renderSuggestions = (payload, currentValue) => {
      const suggestions = payload?.suggestions ?? []
      const lucky = payload?.lucky ?? null

      setLuckyLink(lucky)

      if (!suggestions.length) {
        hideDropdown()
        return
      }

      const luckyHtml = lucky?.url
        ? `
          <a href="${lucky.url}" class="list-group-item list-group-item-action border-primary-subtle" data-search-item>
            <div class="fw-semibold">Buka hasil teratas</div>
            <div class="small text-muted">${lucky.type}: ${lucky.label}</div>
          </a>
        `
        : ''

      const suggestionsHtml = suggestions.map((item) => `
        <a href="${item.url}" class="list-group-item list-group-item-action" data-search-item>
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">${item.label}</div>
              <div class="small text-muted">${item.description ?? ''}</div>
            </div>
            <span class="badge text-bg-light border">${item.type}</span>
          </div>
        </a>
      `).join('')

      dropdown.innerHTML = `
        ${luckyHtml}
        ${suggestionsHtml}
        <button type="submit" class="list-group-item list-group-item-action text-start">
          <div class="fw-semibold">Cari "${currentValue}"</div>
          <div class="small text-muted">Tampilkan semua hasil pencarian</div>
        </button>
      `

      dropdown.classList.remove('d-none')
      activeIndex = -1
    }

    const fetchSuggestions = async () => {
      const query = input.value.trim()

      if (query.length < 2) {
        hideDropdown()
        setLuckyLink(null)
        return
      }

      abortController?.abort()
      abortController = new AbortController()

      try {
        const url = new URL(suggestUrl, window.location.origin)
        url.searchParams.set('q', query)

        const response = await fetch(url.toString(), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          signal: abortController.signal,
        })

        if (!response.ok) {
          hideDropdown()
          return
        }

        const payload = await response.json()
        renderSuggestions(payload, query)
      } catch (error) {
        if (error.name !== 'AbortError') {
          hideDropdown()
        }
      }
    }

    input.addEventListener('input', () => {
      window.clearTimeout(debounceTimer)
      debounceTimer = window.setTimeout(fetchSuggestions, 180)
    })

    input.addEventListener('focus', () => {
      if (input.value.trim().length >= 2) {
        fetchSuggestions()
      }
    })

    input.addEventListener('keydown', (event) => {
      const items = suggestionItems()
      if (!items.length || dropdown.classList.contains('d-none')) return

      if (event.key === 'ArrowDown') {
        event.preventDefault()
        activeIndex = Math.min(activeIndex + 1, items.length - 1)
        updateActiveItem()
      }

      if (event.key === 'ArrowUp') {
        event.preventDefault()
        activeIndex = Math.max(activeIndex - 1, 0)
        updateActiveItem()
      }

      if (event.key === 'Enter' && activeIndex >= 0) {
        event.preventDefault()
        items[activeIndex].click()
      }

      if (event.key === 'Escape') {
        hideDropdown()
      }
    })

    input.addEventListener('blur', () => {
      window.setTimeout(hideDropdown, 150)
    })

    document.addEventListener('click', (event) => {
      if (!form.contains(event.target)) {
        hideDropdown()
      }
    })
  })
}

// Form Validation ( Bootstrap )
class FormValidation {
  initFormValidation() {
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    // Loop over them and prevent submission
    document.querySelectorAll('.needs-validation').forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
  }

  init() {
    this.initFormValidation();
  }
}

//  Form Advanced
class FormAdvanced {

  initMask() {
    document.querySelectorAll('[data-toggle="input-mask"]').forEach(e => {
      const maskFormat = e.getAttribute('data-mask-format').toString().replaceAll('0', '9');
      e.setAttribute("data-mask-format", maskFormat);
      const im = new Inputmask(maskFormat);
      im.mask(e);
    });
  }

  // Choices Select plugin
  initFormChoices() {
    var choicesExamples = document.querySelectorAll("[data-choices]");
    choicesExamples.forEach(function (item) {
      var choiceData = {};
      var isChoicesVal = item.attributes;

      if (isChoicesVal["data-choices-groups"]) {
        choiceData.placeholderValue = "This is a placeholder set in the config";
      }
      if (isChoicesVal["data-choices-search-false"]) {
        choiceData.searchEnabled = false;
      }
      if (isChoicesVal["data-choices-search-true"]) {
        choiceData.searchEnabled = true;
      }
      if (isChoicesVal["data-choices-removeItem"]) {
        choiceData.removeItemButton = true;
      }
      if (isChoicesVal["data-choices-sorting-false"]) {
        choiceData.shouldSort = false;
      }
      if (isChoicesVal["data-choices-sorting-true"]) {
        choiceData.shouldSort = true;
      }
      if (isChoicesVal["data-choices-multiple-remove"]) {
        choiceData.removeItemButton = true;
      }

      // **FIXED:** Correctly set maxItemCount as a number
      if (isChoicesVal["data-choices-limit"]) {
        choiceData.maxItemCount = parseInt(isChoicesVal["data-choices-limit"].value);
      }

      // **FIXED:** Correctly set editItems as a boolean
      if (isChoicesVal["data-choices-editItem-true"]) {
        choiceData.editItems = true;
      }
      if (isChoicesVal["data-choices-editItem-false"]) {
        choiceData.editItems = false;
      }

      if (isChoicesVal["data-choices-text-unique-true"]) {
        choiceData.duplicateItemsAllowed = false;
        choiceData.paste = false;
      }
      if (isChoicesVal["data-choices-text-disabled-true"]) {
        choiceData.addItems = false;
      }

      const choicesInstance = new Choices(item, choiceData);

      // **FIXED:** Conditionally disable the instance after it's created
      if (isChoicesVal["data-choices-text-disabled-true"]) {
        choicesInstance.disable();
      }
    });
  }

  init() {
    this.initMask();
    this.initFormChoices();
  }

}

// Global init
document.addEventListener('DOMContentLoaded', () => {
  initDeleteModals()
  initTableDropdownOverflow()
  initActionDropdowns()
  initSearchAutocomplete()
})

// Dragula (Draggable Components)
class Dragula {

  initDragula() {

    document.querySelectorAll("[data-plugin=dragula]")

      .forEach(function (element) {

        const containersIds = JSON.parse(element.getAttribute('data-containers'));
        let containers = [];
        if (containersIds) {
          for (let i = 0; i < containersIds.length; i++) {
            containers.push(document.querySelectorAll("#" + containersIds[i])[0]);
          }
        } else {
          containers = [element];
        }

        // if handle provided
        const handleClass = element.getAttribute('data-handleclass');

        // init dragula
        if (handleClass) {
          dragula(containers, {
            moves: function (el, container, handle) {
              return handle.classList.contains(handleClass);
            }
          });
        } else {
          dragula(containers);
        }

      });
  }

  init() {
    this.initDragula();
  }

}

// Toast Notification
class ToastNotification {
  initToastNotification() {

    document.querySelectorAll("[data-toast]").forEach(function (element) {
      element.addEventListener("click", function () {
        var toastData = {};
        if (element.attributes["data-toast-text"]) {
          toastData.text = element.attributes["data-toast-text"].value.toString();
        }
        if (element.attributes["data-toast-gravity"]) {
          toastData.gravity = element.attributes["data-toast-gravity"].value.toString();
        }
        if (element.attributes["data-toast-position"]) {
          toastData.position = element.attributes["data-toast-position"].value.toString();
        }
        if (element.attributes["data-toast-className"]) {
          toastData.className = element.attributes["data-toast-className"].value.toString();
        }
        if (element.attributes["data-toast-duration"]) {
          toastData.duration = element.attributes["data-toast-duration"].value.toString();
        }
        if (element.attributes["data-toast-close"]) {
          toastData.close = element.attributes["data-toast-close"].value.toString();
        }
        if (element.attributes["data-toast-style"]) {
          toastData.style = element.attributes["data-toast-style"].value.toString();
        }
        if (element.attributes["data-toast-offset"]) {
          toastData.offset = element.attributes["data-toast-offset"];
        }
        Toastify({
          newWindow: true,
          text: toastData.text,
          gravity: toastData.gravity,
          position: toastData.position,
          className: "bg-" + toastData.className,
          stopOnFocus: true,
          offset: {
            x: toastData.offset ? 50 : 0,
            y: toastData.offset ? 10 : 0, // vertical axis - can be a number or a string indicating unity. eg: '2em'
          },
          duration: toastData.duration,
          close: toastData.close == "close" ? true : false,
        }).showToast();
      });
    });
  }

  init() {
    this.initToastNotification();
  }
}

const initPagePreloader = () => {
  const preloader = document.getElementById('rts__preloader')
  if (!preloader) return

  const hidePreloader = () => {
    preloader.classList.add('is-hidden')
    window.setTimeout(() => {
      preloader.remove()
    }, 500)
  }

  if (document.readyState === 'complete') {
    hidePreloader()
    return
  }

  window.addEventListener('load', hidePreloader, { once: true })
}

document.addEventListener('DOMContentLoaded', function (e) {
  new Components().init();
  new FormValidation().init();
  new FormAdvanced().init();
  new Dragula().init();
  new ToastNotification().init();
  initPagePreloader();
  createIcons({ icons })
});
