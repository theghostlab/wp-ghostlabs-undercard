/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	const __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			const getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop))
/******/ 	})();
/******/ 	
/************************************************************************/

;// external ["wp","plugins"]
const external_wp_plugins_namespaceObject = window["wp"]["plugins"];
;// external ["wp","editor"]
const external_wp_editor_namespaceObject = window["wp"]["editor"];
;// external ["wp","editPost"]
const external_wp_editPost_namespaceObject = window["wp"]["editPost"];
;// external ["wp","coreData"]
const external_wp_coreData_namespaceObject = window["wp"]["coreData"];
;// external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","apiFetch"]
const external_wp_apiFetch_namespaceObject = window["wp"]["apiFetch"];
var external_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_wp_apiFetch_namespaceObject);
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","a11y"]
const external_wp_a11y_namespaceObject = window["wp"]["a11y"];
;// external ["wp","primitives"]
const external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// external "ReactJSXRuntime"
const external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./node_modules/.pnpm/@wordpress+icons@15.4.0_react-dom@18.3.1_react@18.3.1__react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-up.mjs
// packages/icons/src/library/arrow-up.tsx


var arrow_up_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M12 3.9 6.5 9.5l1 1 3.8-3.7V20h1.5V6.8l3.7 3.7 1-1z" }) });

//# sourceMappingURL=arrow-up.mjs.map

;// ./node_modules/.pnpm/@wordpress+icons@15.4.0_react-dom@18.3.1_react@18.3.1__react@18.3.1/node_modules/@wordpress/icons/build-module/library/arrow-down.mjs
// packages/icons/src/library/arrow-down.tsx


var arrow_down_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "m16.5 13.5-3.7 3.7V4h-1.5v13.2l-3.8-3.7-1 1 5.5 5.6 5.5-5.6z" }) });

//# sourceMappingURL=arrow-down.mjs.map

;// ./node_modules/.pnpm/@wordpress+icons@15.4.0_react-dom@18.3.1_react@18.3.1__react@18.3.1/node_modules/@wordpress/icons/build-module/library/close-small.mjs
// packages/icons/src/library/close-small.tsx


var close_small_default = /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.SVG, { xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24", fill: "currentColor", children: /* @__PURE__ */ (0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_primitives_namespaceObject.Path, { d: "M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z" }) });

//# sourceMappingURL=close-small.mjs.map

;// external ["wp","url"]
const external_wp_url_namespaceObject = window["wp"]["url"];
;// ./src/shared/use-author-candidates.jsx




const SEARCH_DELAY_MS = 250;
const SEARCH_RESULTS = 10;
function useAuthorCandidates(restNamespace, term) {
  const [candidates, setCandidates] = (0,external_wp_element_namespaceObject.useState)([]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    let canceled = false;
    const timer = setTimeout(() => {
      external_wp_apiFetch_default()({
        path: (0,external_wp_url_namespaceObject.addQueryArgs)(`/${restNamespace}/authors`, {
          search: term,
          per_page: SEARCH_RESULTS
        })
      }).then(found => {
        if (!canceled) {
          setCandidates(Array.isArray(found) ? found : []);
        }
      }).catch(() => {
        if (!canceled) {
          setCandidates([]);
        }
      });
    }, SEARCH_DELAY_MS);
    return () => {
      canceled = true;
      clearTimeout(timer);
    };
  }, [restNamespace, term]);
  return candidates;
}
function candidateLabel(candidate) {
  return candidate.kind === 'guest' ? (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: a guest author's name. */
  (0,external_wp_i18n_namespaceObject.__)('%s (guest)', 'ghostlabs-undercard'), candidate.name) : candidate.name;
}
;// ./src/editor/byline.jsx















const PluginDocumentSettingPanel = external_wp_editor_namespaceObject.PluginDocumentSettingPanel ?? external_wp_editPost_namespaceObject.PluginDocumentSettingPanel;
const CONFIG = window.GHOSTLABS_UNDERCARD_BYLINE || {
  field: 'ghostlabs_undercard_byline',
  postTypes: [],
  roles: [],
  restNamespace: '',
  upsell: null
};
const UPSELL = CONFIG.upsell || null;
const MINIMUM_CREDITS_FOR_UPSELL = 2;
function Portrait({
  entry
}) {
  if (entry.avatar) {
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("img", {
      className: "ghostlabs-u-byline__portrait",
      src: entry.avatar,
      alt: "",
      width: "24",
      height: "24"
    });
  }
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
    className: "ghostlabs-u-byline__portrait ghostlabs-u-byline__portrait--initial",
    "aria-hidden": "true",
    children: (entry.name || '?').trim().charAt(0).toUpperCase()
  });
}
function Row({
  entry,
  index,
  total,
  onRole,
  onMove,
  onRemove
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("li", {
    className: "ghostlabs-u-byline__row",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Flex, {
      align: "center",
      gap: 2,
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Portrait, {
          entry: entry
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.FlexBlock, {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "ghostlabs-u-byline__name",
          children: entry.name
        }), entry.kind === 'guest' && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("span", {
          className: "ghostlabs-u-byline__kind",
          children: (0,external_wp_i18n_namespaceObject.__)('Guest', 'ghostlabs-undercard')
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
          size: "small",
          icon: arrow_up_default,
          disabled: index === 0,
          accessibleWhenDisabled: true,
          label: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: an author's name. */
          (0,external_wp_i18n_namespaceObject.__)('Move %s up', 'ghostlabs-undercard'), entry.name),
          onClick: () => onMove(index, -1)
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
          size: "small",
          icon: arrow_down_default,
          disabled: index === total - 1,
          accessibleWhenDisabled: true,
          label: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: an author's name. */
          (0,external_wp_i18n_namespaceObject.__)('Move %s down', 'ghostlabs-undercard'), entry.name),
          onClick: () => onMove(index, 1)
        })
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.FlexItem, {
        children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
          size: "small",
          icon: close_small_default,
          isDestructive: true,
          className: "ghostlabs-u-byline__remove",
          "data-credit": entry.id,
          label: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: an author's name. */
          (0,external_wp_i18n_namespaceObject.__)('Remove %s', 'ghostlabs-undercard'), entry.name),
          onClick: () => onRemove(index)
        })
      })]
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
      className: "ghostlabs-u-byline__role",
      label: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: an author's name. */
      (0,external_wp_i18n_namespaceObject.__)('Role for %s', 'ghostlabs-undercard'), entry.name),
      hideLabelFromVision: true,
      value: entry.role,
      options: CONFIG.roles,
      onChange: role => onRole(index, role),
      __nextHasNoMarginBottom: true,
      __next40pxDefaultSize: true
    })]
  });
}
function UpsellNotice({
  credits
}) {
  const [dismissed, setDismissed] = (0,external_wp_element_namespaceObject.useState)(UPSELL ? UPSELL.dismissed : true);
  if (!UPSELL || dismissed || credits < MINIMUM_CREDITS_FOR_UPSELL) {
    return null;
  }
  const dismiss = () => {
    setDismissed(true);
    external_wp_apiFetch_default()({
      path: '/wp/v2/users/me',
      method: 'POST',
      data: {
        meta: {
          [UPSELL.metaKey]: true
        }
      }
    }).catch(() => {});
    (0,external_wp_a11y_namespaceObject.speak)((0,external_wp_i18n_namespaceObject.__)('Upgrade prompt dismissed.', 'ghostlabs-undercard'));
    let frames = 0;
    const settle = () => {
      if (document.querySelector('.ghostlabs-u-byline__upsell') && frames++ < 30) {
        window.requestAnimationFrame(settle);
        return;
      }
      document.querySelector('.ghostlabs-u-byline__picker input[role="combobox"]')?.focus();
    };
    window.requestAnimationFrame(settle);
  };
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.Notice, {
    className: "ghostlabs-u-byline__upsell",
    status: "info",
    onRemove: dismiss,
    spokenMessage: "",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "ghostlabs-u-byline__upsell-title",
      children: /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("strong", {
        children: (0,external_wp_i18n_namespaceObject.__)('More than one person is credited.', 'ghostlabs-undercard')
      })
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      children: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: the name of the paid edition. */
      (0,external_wp_i18n_namespaceObject.__)('%s emits each of them in the article’s structured data, with their roles.', 'ghostlabs-undercard'), UPSELL.editionName)
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ExternalLink, {
      href: UPSELL.url,
      children: (0,external_wp_i18n_namespaceObject.__)('See what that publishes', 'ghostlabs-undercard')
    })]
  });
}
function Byline() {
  const postType = (0,external_wp_data_namespaceObject.useSelect)(select => select(external_wp_editor_namespaceObject.store).getCurrentPostType(), []);
  const [stored, setStored] = (0,external_wp_coreData_namespaceObject.useEntityProp)('postType', postType, CONFIG.field);
  const byline = (0,external_wp_element_namespaceObject.useMemo)(() => Array.isArray(stored) ? stored : [], [stored]);
  const [term, setTerm] = (0,external_wp_element_namespaceObject.useState)('');
  const candidates = useAuthorCandidates(CONFIG.restNamespace, term);
  const options = (0,external_wp_element_namespaceObject.useMemo)(() => {
    const taken = new Set(byline.map(entry => entry.id));
    return candidates.filter(candidate => !taken.has(candidate.id)).map(candidate => ({
      value: candidate.id,
      label: candidateLabel(candidate)
    }));
  }, [candidates, byline]);
  if (!postType || !CONFIG.postTypes.includes(postType)) {
    return null;
  }
  const add = id => {
    const candidate = candidates.find(each => each.id === id);
    if (!candidate) {
      return;
    }
    setStored([...byline, {
      ...candidate,
      role: 'author'
    }]);
    setTerm('');
    (0,external_wp_a11y_namespaceObject.speak)((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: 1: an author's name. 2: their position. 3: how many people the byline names. */
    (0,external_wp_i18n_namespaceObject.__)('%1$s added to the byline, position %2$d of %3$d.', 'ghostlabs-undercard'), candidate.name, byline.length + 1, byline.length + 1));
  };
  const move = (index, delta) => {
    const next = [...byline];
    const target = index + delta;
    if (target < 0 || target >= byline.length) {
      return;
    }
    [next[index], next[target]] = [next[target], next[index]];
    setStored(next);
    (0,external_wp_a11y_namespaceObject.speak)((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: 1: an author's name. 2: their new position. 3: how many people the byline names. */
    (0,external_wp_i18n_namespaceObject.__)('%1$s moved to position %2$d of %3$d.', 'ghostlabs-undercard'), byline[index].name, target + 1, next.length));
  };
  const setRole = (index, role) => {
    setStored(byline.map((entry, at) => at === index ? {
      ...entry,
      role
    } : entry));
    (0,external_wp_a11y_namespaceObject.speak)((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: 1: an author's name. 2: the role they hold on this post. */
    (0,external_wp_i18n_namespaceObject.__)('%1$s is now credited as %2$s.', 'ghostlabs-undercard'), byline[index].name, CONFIG.roles.find(each => each.value === role)?.label ?? role));
  };
  const pendingFocus = (0,external_wp_element_namespaceObject.useRef)(null);
  const remove = index => {
    const going = byline[index];
    const next = byline[index + 1] ?? byline[index - 1] ?? null;
    pendingFocus.current = {
      gone: going.id,
      next: next ? next.id : null
    };
    setStored(byline.filter((_, at) => at !== index));
    (0,external_wp_a11y_namespaceObject.speak)((0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: an author's name. */
    (0,external_wp_i18n_namespaceObject.__)('%s removed from the byline.', 'ghostlabs-undercard'), going.name), 'assertive');
  };
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const pending = pendingFocus.current;
    if (!pending) {
      return;
    }
    pendingFocus.current = null;
    const button = id => document.querySelector(`.ghostlabs-u-byline__remove[data-credit="${window.CSS.escape(id)}"]`);
    let frames = 0;
    const settle = () => {
      if (button(pending.gone) && frames++ < 30) {
        window.requestAnimationFrame(settle);
        return;
      }
      const target = pending.next ? button(pending.next) : document.querySelector('.ghostlabs-u-byline__picker input[role="combobox"]');
      target?.focus();
    };
    window.requestAnimationFrame(settle);
  }, [byline]);
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(PluginDocumentSettingPanel, {
    name: "ghostlabs-undercard-byline",
    title: (0,external_wp_i18n_namespaceObject.__)('Byline', 'ghostlabs-undercard'),
    className: "ghostlabs-u-byline",
    children: [byline.length === 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
      className: "ghostlabs-u-byline__empty",
      children: (0,external_wp_i18n_namespaceObject.__)('No byline set, so this post follows its author. Add somebody to credit them instead.', 'ghostlabs-undercard')
    }), byline.length > 0 && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("ul", {
      className: "ghostlabs-u-byline__list",
      children: byline.map((entry, index) => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(Row, {
        entry: entry,
        index: index,
        total: byline.length,
        onRole: setRole,
        onMove: move,
        onRemove: remove
      }, entry.id))
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.ComboboxControl, {
      className: "ghostlabs-u-byline__picker",
      label: (0,external_wp_i18n_namespaceObject.__)('Add somebody', 'ghostlabs-undercard'),
      help: (0,external_wp_i18n_namespaceObject.__)('Registered users and guest authors, in one list.', 'ghostlabs-undercard'),
      value: null,
      options: options,
      onFilterValueChange: setTerm,
      onChange: add,
      __nextHasNoMarginBottom: true,
      __next40pxDefaultSize: true
    }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(UpsellNotice, {
      credits: byline.length
    })]
  });
}
;(0,external_wp_plugins_namespaceObject.registerPlugin)('ghostlabs-undercard-byline', {
  render: Byline
});
/******/ })()
;