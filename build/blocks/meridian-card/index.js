/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ 463
(__unused_webpack_module, __unused_webpack___webpack_exports__, __webpack_require__) {


;// external ["wp","blocks"]
const external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// external ["wp","blockEditor"]
const external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","serverSideRender"]
const external_wp_serverSideRender_namespaceObject = window["wp"]["serverSideRender"];
var external_wp_serverSideRender_default = /*#__PURE__*/__webpack_require__.n(external_wp_serverSideRender_namespaceObject);
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","coreData"]
const external_wp_coreData_namespaceObject = window["wp"]["coreData"];
;// external ["wp","apiFetch"]
const external_wp_apiFetch_namespaceObject = window["wp"]["apiFetch"];
var external_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_wp_apiFetch_namespaceObject);
;// external "ReactJSXRuntime"
const external_ReactJSXRuntime_namespaceObject = window["ReactJSXRuntime"];
;// ./src/shared/use-avatar-upload.jsx
/* unused harmony import specifier */ var useSelect;
/* unused harmony import specifier */ var coreStore;







function useCardAuthorId(attributeAuthorId) {
  return (0,external_wp_data_namespaceObject.useSelect)(select => {
    if (attributeAuthorId) {
      return attributeAuthorId;
    }
    const editor = select('core/editor');
    return editor ? editor.getEditedPostAttribute('author') : 0;
  }, [attributeAuthorId]);
}
function useAuthorPhotoUrl(authorId, bust = 0) {
  return useSelect(select => {
    if (!authorId) {
      return '';
    }
    const {
      getEntityRecord
    } = select(coreStore);
    const user = getEntityRecord('root', 'user', authorId);
    const attachmentId = Number(user?.meta?.undercard_avatar_id ?? 0);
    if (!attachmentId) {
      return '';
    }
    const media = getEntityRecord('postType', 'attachment', attachmentId);
    return media?.source_url ?? '';
  }, [authorId, bust]);
}
function useAvatarUpload({
  authorId,
  containerRef
}) {
  const [bust, setBust] = (0,external_wp_element_namespaceObject.useState)(0);
  const openRef = (0,external_wp_element_namespaceObject.useRef)(null);
  const fallbackRef = (0,external_wp_element_namespaceObject.useRef)(null);
  const canEdit = (0,external_wp_data_namespaceObject.useSelect)(select => authorId ? select(external_wp_coreData_namespaceObject.store).canUser('update', 'users', authorId) === true : false, [authorId]);
  const save = (0,external_wp_element_namespaceObject.useCallback)(async attachmentId => {
    if (!authorId) {
      return;
    }
    await external_wp_apiFetch_default()({
      path: `/wp/v2/users/${authorId}`,
      method: 'POST',
      data: {
        meta: {
          undercard_avatar_id: attachmentId
        }
      }
    });
    setBust(n => n + 1);
  }, [authorId]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const root = containerRef.current;
    if (!root || !canEdit) {
      return undefined;
    }
    const decorate = () => {
      const node = root.querySelector('[data-uc-avatar]');
      if (!node || node.dataset.ucUploadReady) {
        return;
      }
      node.dataset.ucUploadReady = '1';
      node.classList.add('ghostlabs-u-avatar-upload');
      node.setAttribute('role', 'button');
      node.setAttribute('tabindex', '0');
      node.setAttribute('aria-label', (0,external_wp_i18n_namespaceObject.__)('Upload a profile photo', 'ghostlabs-undercard'));
      node.removeAttribute('aria-hidden');
    };
    decorate();
    const observer = new MutationObserver(decorate);
    observer.observe(root, {
      childList: true,
      subtree: true
    });
    return () => observer.disconnect();
  }, [canEdit, containerRef, bust]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const root = containerRef.current;
    if (!root || !canEdit) {
      return undefined;
    }
    const hit = event => typeof event.target?.closest === 'function' ? event.target.closest('[data-uc-avatar]') : null;
    const onClick = event => {
      if (!hit(event)) {
        return;
      }
      event.preventDefault();
      event.stopPropagation();
      openRef.current ? openRef.current() : fallbackRef.current?.();
    };
    const onKeyDown = event => {
      if (event.key !== 'Enter' && event.key !== ' ' || !hit(event)) {
        return;
      }
      event.preventDefault();
      openRef.current ? openRef.current() : fallbackRef.current?.();
    };
    root.addEventListener('click', onClick, true);
    root.addEventListener('keydown', onKeyDown, true);
    return () => {
      root.removeEventListener('click', onClick, true);
      root.removeEventListener('keydown', onKeyDown, true);
    };
  }, [canEdit, containerRef]);
  const control = canEdit ? /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_blockEditor_namespaceObject.MediaUpload, {
    onSelect: media => save(media.id),
    allowedTypes: ['image'],
    title: (0,external_wp_i18n_namespaceObject.__)('Choose a profile photo', 'ghostlabs-undercard'),
    render: ({
      open
    }) => {
      openRef.current = open;
      return null;
    }
  }) : null;
  const open = (0,external_wp_element_namespaceObject.useCallback)(() => {
    if (openRef.current) {
      openRef.current();
      return;
    }
    const media = window.wp?.media;
    if (!media) {
      return;
    }
    const frame = media({
      title: (0,external_wp_i18n_namespaceObject.__)('Choose a profile photo', 'ghostlabs-undercard'),
      button: {
        text: (0,external_wp_i18n_namespaceObject.__)('Use this photo', 'ghostlabs-undercard')
      },
      library: {
        type: 'image'
      },
      multiple: false
    });
    frame.on('select', () => {
      const selected = frame.state().get('selection').first();
      if (selected) {
        save(selected.toJSON().id);
      }
    });
    frame.open();
  }, [save]);
  fallbackRef.current = open;
  return {
    canEdit,
    control,
    bust,
    queryArgs: {
      _uc_avatar: bust
    },
    open,
    remove: () => save(0)
  };
}
;// ./src/shared/use-inert-links.jsx

const INTERACTIVE = 'a[href], button';
function useInertLinks(containerRef) {
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const root = containerRef.current;
    if (!root) {
      return undefined;
    }
    const deaden = () => {
      for (const node of root.querySelectorAll(INTERACTIVE)) {
        if (!node.hasAttribute('inert') && !node.closest('[data-uc-avatar]')) {
          node.setAttribute('inert', '');
        }
      }
    };
    deaden();
    const observer = new MutationObserver(deaden);
    observer.observe(root, {
      childList: true,
      subtree: true
    });
    return () => observer.disconnect();
  }, [containerRef]);
}
;// ./src/shared/heading-level-control.jsx



function HeadingLevelControl({
  value,
  onChange
}) {
  return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
    label: (0,external_wp_i18n_namespaceObject.__)('Name heading level', 'ghostlabs-undercard'),
    value: String(value ?? 2),
    help: (0,external_wp_i18n_namespaceObject.__)('Match the page around the card. A card inside a post usually sits at 2, below the post title; one nested under a section heading may need a lower level.', 'ghostlabs-undercard'),
    options: [2, 3, 4, 5, 6].map(level => ({
      label: (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %d: a heading level, 2 to 6. */
      (0,external_wp_i18n_namespaceObject.__)('Heading %d', 'ghostlabs-undercard'), level),
      value: String(level)
    })),
    onChange: level => onChange(Number(level)),
    __nextHasNoMarginBottom: true
  });
}
;// ./src/shared/block-icons.jsx
/* unused harmony import specifier */ var _jsxs;
/* unused harmony import specifier */ var _Fragment;
/* unused harmony import specifier */ var _jsx;

const EMBER = '#E8501F';
const INK = '#0F1B2D';
const IVORY = '#F9F7F2';
const backCard = /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("g", {
  transform: "rotate(14 16.107 11.85)",
  children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "10.945",
    y: "4.01",
    width: "10.325",
    height: "15.68",
    rx: "1.737",
    fill: EMBER
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "14.37",
    y: "7.85",
    width: "5.404",
    height: "1.6",
    rx: "0.8",
    fill: INK
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "14.37",
    y: "11.05",
    width: "3.908",
    height: "1.6",
    rx: "0.8",
    fill: INK
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "14.37",
    y: "14.25",
    width: "4.921",
    height: "1.6",
    rx: "0.8",
    fill: INK
  })]
});
const markWithFace = face => /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("svg", {
  width: "24",
  height: "24",
  viewBox: "0 0 24 24",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg",
  "aria-hidden": "true",
  focusable: "false",
  children: [backCard, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("g", {
    transform: "rotate(-6 6.941 12.67)",
    children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
      x: "1.778",
      y: "4.83",
      width: "10.325",
      height: "15.68",
      rx: "1.737",
      fill: INK
    }), face]
  })]
});
const MeridianIcon = /*#__PURE__*/markWithFace(/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
  children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "3.49",
    y: "6.9",
    width: "6.9",
    height: "2.2",
    rx: "1.1",
    fill: IVORY
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "3.49",
    y: "10.9",
    width: "6.2",
    height: "1.6",
    rx: "0.8",
    fill: IVORY
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "3.49",
    y: "14.1",
    width: "4.4",
    height: "1.6",
    rx: "0.8",
    fill: IVORY
  }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("rect", {
    x: "3.49",
    y: "17.3",
    width: "5.6",
    height: "1.6",
    rx: "0.8",
    fill: IVORY
  })]
}));
const AuroraIcon = /*#__PURE__*/(/* unused pure expression or super */ null && (markWithFace(/*#__PURE__*/_jsxs(_Fragment, {
  children: [/*#__PURE__*/_jsx("circle", {
    cx: "6.941",
    cy: "9.4",
    r: "2.34",
    fill: IVORY
  }), /*#__PURE__*/_jsx("rect", {
    x: "3.49",
    y: "13.2",
    width: "6.9",
    height: "1.6",
    rx: "0.8",
    fill: IVORY
  }), /*#__PURE__*/_jsx("rect", {
    x: "3.49",
    y: "16.6",
    width: "1.9",
    height: "1.6",
    rx: "0.4",
    fill: IVORY
  }), /*#__PURE__*/_jsx("rect", {
    x: "5.99",
    y: "16.6",
    width: "1.9",
    height: "1.6",
    rx: "0.4",
    fill: IVORY
  }), /*#__PURE__*/_jsx("rect", {
    x: "8.49",
    y: "16.6",
    width: "1.9",
    height: "1.6",
    rx: "0.4",
    fill: IVORY
  })]
}))));
const CobaltIcon = /*#__PURE__*/(/* unused pure expression or super */ null && (markWithFace(/*#__PURE__*/_jsxs(_Fragment, {
  children: [/*#__PURE__*/_jsx("rect", {
    x: "3.49",
    y: "6.4",
    width: "6.9",
    height: "6.2",
    rx: "1",
    fill: IVORY
  }), /*#__PURE__*/_jsx("circle", {
    cx: "6.941",
    cy: "8.9",
    r: "1.45",
    fill: INK
  }), /*#__PURE__*/_jsx("path", {
    d: "M 4.55 12.6 V 12.48 A 2.39 2.39 0 0 1 9.33 12.48 V 12.6 Z",
    fill: INK
  }), /*#__PURE__*/_jsx("rect", {
    x: "3.49",
    y: "14.2",
    width: "6.9",
    height: "1.9",
    rx: "0.95",
    fill: IVORY
  }), /*#__PURE__*/_jsx("rect", {
    x: "3.49",
    y: "17.4",
    width: "4.6",
    height: "1.6",
    rx: "0.8",
    fill: IVORY
  })]
}))));
const NfcIcon = /*#__PURE__*/(/* unused pure expression or super */ null && (markWithFace(/*#__PURE__*/_jsxs("g", {
  stroke: IVORY,
  strokeWidth: "1.4",
  strokeLinecap: "round",
  fill: "none",
  children: [/*#__PURE__*/_jsx("path", {
    d: "M 4.26 10.974 A 2 2 0 0 1 4.26 14.366"
  }), /*#__PURE__*/_jsx("path", {
    d: "M 5.638 8.769 A 4.6 4.6 0 0 1 5.638 16.571"
  }), /*#__PURE__*/_jsx("path", {
    d: "M 7.015 6.564 A 7.2 7.2 0 0 1 7.015 18.776"
  })]
}))));
;// ./src/blocks/meridian-card/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"ghostlabs/undercard-meridian"}');
;// ./src/blocks/meridian-card/index.jsx














(0,external_wp_blocks_namespaceObject.registerBlockType)(block_namespaceObject.UU, {
  icon: MeridianIcon,
  edit({
    attributes,
    setAttributes,
    context
  }) {
    const ref = (0,external_wp_element_namespaceObject.useRef)();
    const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)({
      ref
    });
    const authorId = useCardAuthorId(attributes.authorId);
    const avatar = useAvatarUpload({
      authorId,
      containerRef: ref
    });
    useInertLinks(ref);
    return /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_ReactJSXRuntime_namespaceObject.Fragment, {
      children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_blockEditor_namespaceObject.InspectorControls, {
        children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
          title: (0,external_wp_i18n_namespaceObject.__)('Card', 'ghostlabs-undercard'),
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
            label: (0,external_wp_i18n_namespaceObject.__)('Layout', 'ghostlabs-undercard'),
            value: attributes.layout,
            options: [{
              label: (0,external_wp_i18n_namespaceObject.__)('Vertical', 'ghostlabs-undercard'),
              value: 'vertical'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('Horizontal', 'ghostlabs-undercard'),
              value: 'horizontal'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('Row', 'ghostlabs-undercard'),
              value: 'row'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('Chip', 'ghostlabs-undercard'),
              value: 'chip'
            }],
            onChange: layout => setAttributes({
              layout
            }),
            __nextHasNoMarginBottom: true
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
            label: (0,external_wp_i18n_namespaceObject.__)('Avatar', 'ghostlabs-undercard'),
            value: attributes.avatar,
            help: (0,external_wp_i18n_namespaceObject.__)('Monogram always renders; Photo needs an uploaded avatar and falls back to the monogram.', 'ghostlabs-undercard'),
            options: [{
              label: (0,external_wp_i18n_namespaceObject.__)('Monogram', 'ghostlabs-undercard'),
              value: 'monogram'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('Photo', 'ghostlabs-undercard'),
              value: 'photo'
            }],
            onChange: avatar => setAttributes({
              avatar
            }),
            __nextHasNoMarginBottom: true
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.SelectControl, {
            label: (0,external_wp_i18n_namespaceObject.__)('Theme', 'ghostlabs-undercard'),
            value: attributes.scheme,
            help: (0,external_wp_i18n_namespaceObject.__)('System follows the visitor’s device preference.', 'ghostlabs-undercard'),
            options: [{
              label: (0,external_wp_i18n_namespaceObject.__)('Midnight', 'ghostlabs-undercard'),
              value: 'dark'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('Ivory', 'ghostlabs-undercard'),
              value: 'light'
            }, {
              label: (0,external_wp_i18n_namespaceObject.__)('System', 'ghostlabs-undercard'),
              value: 'system'
            }],
            onChange: scheme => setAttributes({
              scheme
            }),
            __nextHasNoMarginBottom: true
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(HeadingLevelControl, {
            value: attributes.headingLevel,
            onChange: headingLevel => setAttributes({
              headingLevel
            })
          })]
        }), avatar.canEdit && /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)(external_wp_components_namespaceObject.PanelBody, {
          title: (0,external_wp_i18n_namespaceObject.__)('Profile photo', 'ghostlabs-undercard'),
          initialOpen: false,
          children: [/*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)("p", {
            children: (0,external_wp_i18n_namespaceObject.__)('Saved to the author’s profile, not to this block — every card showing them updates.', 'ghostlabs-undercard')
          }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
            variant: "secondary",
            onClick: avatar.open,
            children: (0,external_wp_i18n_namespaceObject.__)('Choose photo', 'ghostlabs-undercard')
          }), ' ', /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)(external_wp_components_namespaceObject.Button, {
            variant: "tertiary",
            isDestructive: true,
            onClick: avatar.remove,
            children: (0,external_wp_i18n_namespaceObject.__)('Remove', 'ghostlabs-undercard')
          })]
        })]
      }), /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsxs)("div", {
        ...blockProps,
        children: [avatar.control, /*#__PURE__*/(0,external_ReactJSXRuntime_namespaceObject.jsx)((external_wp_serverSideRender_default()), {
          block: block_namespaceObject.UU,
          attributes: attributes,
          urlQueryArgs: {
            ...avatar.queryArgs,
            ...(context?.postId ? {
              post_id: context.postId
            } : {})
          }
        })]
      })]
    });
  },
  save() {
    return null;
  }
});

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		const deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			let notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				let [chunkIds, fn, priority] = deferred[i];
/******/ 				let fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					const r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			105: 0,
/******/ 			709: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		const webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			let [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkundercard_free"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, [709], () => (__webpack_require__(463)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;