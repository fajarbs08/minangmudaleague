/**
* Dashboard App Script
* Liga Anak Piaman Laweh
*/

import Collapse from 'bootstrap/js/dist/collapse'
import Dropdown from 'bootstrap/js/dist/dropdown'
import Modal from 'bootstrap/js/dist/modal'
import Offcanvas from 'bootstrap/js/dist/offcanvas'
import Popover from 'bootstrap/js/dist/popover'
import Toast from 'bootstrap/js/dist/toast'
import Tooltip from 'bootstrap/js/dist/tooltip'

window.bootstrap = {
  Collapse,
  Dropdown,
  Modal,
  Offcanvas,
  Popover,
  Toast,
  Tooltip,
}

import 'simplebar'
import dragula from 'dragula';

window.dragula = dragula;

if (document.querySelector('iconify-icon')) {
  void import('iconify-icon')
}

const lucideIconsPromise = document.querySelector('[data-lucide]')
  ? import('./lucide-icons')
  : null

let choicesPromise
const loadChoices = () => {
  if (!choicesPromise) {
    choicesPromise = import('choices.js').then(({ default: Choices }) => Choices)
  }

  return choicesPromise
}

// Components
class Components {

  initBootstrapComponents() {

    // Popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new Popover(popoverTriggerEl))

    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))

    // offcanvas
    const offcanvasElementList = document.querySelectorAll('.offcanvas')
    const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new Offcanvas(offcanvasEl))

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
      return new Toast(toastEl)
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
  const measureMenuHeight = (menu) => {
    const previousDisplay = menu.style.display
    const previousVisibility = menu.style.visibility
    const previousPosition = menu.style.position

    menu.style.display = 'block'
    menu.style.visibility = 'hidden'
    menu.style.position = 'absolute'

    const height = menu.offsetHeight

    menu.style.display = previousDisplay
    menu.style.visibility = previousVisibility
    menu.style.position = previousPosition

    return height
  }

  const syncDropdownDirection = (button) => {
    const dropdown = button.closest('.dropdown, .dropup')
    const menu = dropdown?.querySelector('.competition-action-menu, .dropdown-menu')
    if (!dropdown || !menu) return

    dropdown.classList.remove('dropup')
    dropdown.classList.add('dropdown')

    const buttonRect = button.getBoundingClientRect()
    const menuHeight = Math.min(measureMenuHeight(menu), window.innerHeight * 0.7)
    const spaceBelow = window.innerHeight - buttonRect.bottom
    const spaceAbove = buttonRect.top

    if (spaceBelow < menuHeight + 16 && spaceAbove > spaceBelow) {
      dropdown.classList.remove('dropdown')
      dropdown.classList.add('dropup')
    }
  }

  document.querySelectorAll('.competition-action-toggle').forEach((button) => {
    button.setAttribute('data-bs-display', 'static')
    button.setAttribute('data-bs-offset', '0,4')
    button.setAttribute('data-bs-boundary', 'viewport')

    button.addEventListener('click', () => {
      syncDropdownDirection(button)
    })
  })

  window.addEventListener('resize', () => {
    document.querySelectorAll('.competition-action-toggle[aria-expanded="true"]').forEach((button) => {
      syncDropdownDirection(button)
    })
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

const initFormDraftAutosave = () => {
  const storagePrefix = 'lap-dashboard:form-draft'
  const skippedInputTypes = new Set(['button', 'submit', 'reset', 'file', 'password'])
  const skippedNames = new Set(['_token', '_method'])

  const shouldAutosaveForm = (form) => {
    if (form.dataset.autosave === 'off') return false
    if (form.matches('.authentication-form, .review-actions-form')) return false
    if (form.closest('.modal')) return false

    const method = (form.getAttribute('method') || 'GET').toUpperCase()
    if (method === 'GET') return false

    const id = form.id || ''
    if (id.startsWith('delete-') || id.startsWith('bulk-')) return false
    if (id.includes('delete') || id.includes('bulk')) return false

    const action = form.getAttribute('action') || ''
    if (action.includes('/logout') || action.includes('/login')) return false
    if (action.includes('bulk-review')) return false

    return true
  }

  const draftKeyFor = (form, index) => {
    const action = form.getAttribute('action') || window.location.pathname
    const method = (form.getAttribute('method') || 'GET').toUpperCase()
    const formId = form.id || `form-${index}`

    return `${storagePrefix}:${window.location.pathname}:${method}:${action}:${formId}`
  }

  const controlsFor = (form) => {
    return [...form.elements].filter((field) => {
      if (!field.name || field.disabled) return false
      if (skippedNames.has(field.name)) return false
      if (field.matches('[data-autosave-ignore]')) return false

      const type = (field.type || '').toLowerCase()
      return !skippedInputTypes.has(type)
    })
  }

  const serializeForm = (form) => {
    const payload = {}
    const grouped = new Map()

    controlsFor(form).forEach((field) => {
      if (!grouped.has(field.name)) {
        grouped.set(field.name, [])
      }

      grouped.get(field.name).push(field)
    })

    grouped.forEach((fields, name) => {
      const first = fields[0]
      const type = (first.type || '').toLowerCase()

      if (type === 'checkbox') {
        payload[name] = fields.filter((field) => field.checked).map((field) => field.value)
        return
      }

      if (type === 'radio') {
        payload[name] = fields.find((field) => field.checked)?.value ?? null
        return
      }

      if (first.tagName === 'SELECT' && first.multiple) {
        payload[name] = [...first.selectedOptions].map((option) => option.value)
        return
      }

      if (fields.length > 1) {
        payload[name] = fields.map((field) => field.value)
        return
      }

      payload[name] = first.value
    })

    return payload
  }

  const restoreForm = (form, payload) => {
    if (!payload || typeof payload !== 'object') return

    const grouped = new Map()
    controlsFor(form).forEach((field) => {
      if (!grouped.has(field.name)) {
        grouped.set(field.name, [])
      }

      grouped.get(field.name).push(field)
    })

    Object.entries(payload).forEach(([name, value]) => {
      const fields = grouped.get(name)
      if (!fields?.length) return

      const first = fields[0]
      const type = (first.type || '').toLowerCase()

      if (type === 'checkbox') {
        const values = Array.isArray(value) ? value.map(String) : []
        fields.forEach((field) => {
          field.checked = values.includes(String(field.value))
          field.dispatchEvent(new Event('change', { bubbles: true }))
        })
        return
      }

      if (type === 'radio') {
        fields.forEach((field) => {
          field.checked = String(field.value) === String(value)
          field.dispatchEvent(new Event('change', { bubbles: true }))
        })
        return
      }

      if (first.tagName === 'SELECT' && first.multiple) {
        const values = Array.isArray(value) ? value.map(String) : []
        ;[...first.options].forEach((option) => {
          option.selected = values.includes(String(option.value))
        })
        first.dispatchEvent(new Event('change', { bubbles: true }))
        return
      }

      if (fields.length > 1 && Array.isArray(value)) {
        fields.forEach((field, fieldIndex) => {
          field.value = value[fieldIndex] ?? ''
          field.dispatchEvent(new Event('input', { bubbles: true }))
          field.dispatchEvent(new Event('change', { bubbles: true }))
        })
        return
      }

      first.value = value ?? ''
      first.dispatchEvent(new Event('input', { bubbles: true }))
      first.dispatchEvent(new Event('change', { bubbles: true }))
    })
  }

  document.querySelectorAll('form').forEach((form, index) => {
    if (!shouldAutosaveForm(form)) return

    const draftKey = draftKeyFor(form, index)
    const rawDraft = localStorage.getItem(draftKey)
    let saveTimer = null
    let restoring = false

    if (rawDraft) {
      try {
        restoring = true
        restoreForm(form, JSON.parse(rawDraft).fields)
      } catch (error) {
        localStorage.removeItem(draftKey)
      } finally {
        restoring = false
      }
    }

    const saveDraft = () => {
      if (restoring) return

      window.clearTimeout(saveTimer)
      saveTimer = window.setTimeout(() => {
        localStorage.setItem(draftKey, JSON.stringify({
          fields: serializeForm(form),
          savedAt: new Date().toISOString(),
        }))
      }, 250)
    }

    form.addEventListener('input', saveDraft)
    form.addEventListener('change', saveDraft)
    form.addEventListener('submit', () => {
      localStorage.removeItem(draftKey)
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
  // Choices Select plugin
  async initFormChoices() {
    var choicesExamples = document.querySelectorAll("[data-choices]");
    if (!choicesExamples.length) {
      return;
    }

    const Choices = await loadChoices();

    choicesExamples.forEach(function (item) {
      if (item.dataset.choicesInitialized === 'true') {
        return;
      }

      item.dataset.choicesInitialized = 'true';

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

      if (isChoicesVal["data-bulk-choices"]) {
        choiceData.searchEnabled = false;
        choiceData.shouldSort = false;
        choiceData.itemSelectText = '';
        choiceData.closeDropdownOnSelect = true;
        choiceData.position = 'bottom';
      }

      const choicesInstance = new Choices(item, choiceData);
      const isSingleSelect = item.tagName === 'SELECT' && !item.multiple;
      const container = choicesInstance.containerOuter ? choicesInstance.containerOuter.element : null;
      const searchInput = choicesInstance.input ? choicesInstance.input.element : null;
      const dropdown = choicesInstance.dropdown ? choicesInstance.dropdown.element : null;

      const closeChoicesDropdown = function () {
        if (!isSingleSelect) {
          return;
        }

        window.setTimeout(function () {
          choicesInstance.hideDropdown(true);

          if (container) {
            container.classList.remove('is-open', 'is-focused');
            container.setAttribute('aria-expanded', 'false');
            container.blur();
          }

          if (dropdown) {
            dropdown.setAttribute('aria-expanded', 'false');
          }

          if (searchInput) {
            searchInput.blur();
          }
        }, 0);
      };

      item.addEventListener('choice', closeChoicesDropdown);
      item.addEventListener('addItem', closeChoicesDropdown);
      item.addEventListener('change', closeChoicesDropdown);

      if (container) {
        const closeFromChoiceItem = function (event) {
          const selectableChoice = event.target.closest('.choices__item--selectable');

          if (!selectableChoice || (dropdown && !dropdown.contains(selectableChoice))) {
            return;
          }

          closeChoicesDropdown();
        };

        container.addEventListener('click', closeFromChoiceItem);
        container.addEventListener('touchend', closeFromChoiceItem, { passive: true });

        document.addEventListener('pointerdown', function (event) {
          if (container.contains(event.target)) {
            return;
          }

          closeChoicesDropdown();
        });

        container.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') {
            closeChoicesDropdown();
          }
        });
      }

      // **FIXED:** Conditionally disable the instance after it's created
      if (isChoicesVal["data-choices-text-disabled-true"]) {
        choicesInstance.disable();
      }
    });
  }

  init() {
    void this.initFormChoices();
  }

}

// Global init
document.addEventListener('DOMContentLoaded', () => {
  initDeleteModals()
  initTableDropdownOverflow()
  initActionDropdowns()
  initSearchAutocomplete()
  initFormDraftAutosave()
})

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
  initPagePreloader();

  if (lucideIconsPromise) {
    void lucideIconsPromise.then(({ createIcons, appIcons }) => {
      createIcons({ icons: appIcons })
    })
  }
});
