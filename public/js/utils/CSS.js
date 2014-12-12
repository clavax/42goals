window.CSS = new Singletone({
    getRule: function (ruleName, deleteFlag) {
        ruleName = ruleName.toLowerCase();
        if (document.styleSheets) {
            for (var i = 0; i < document.styleSheets.length; i ++) {
                var styleSheet = document.styleSheets[i];
                var j = 0;
                var cssRule = false; 
                do {
                    if (styleSheet.cssRules) {
                        cssRule = styleSheet.cssRules[j];
                    } else {
                        cssRule = styleSheet.rules[j]; 
                    }
                    if (cssRule && cssRule.selectorText) {
                        if (cssRule.selectorText.toLowerCase() == ruleName) {
                            if (deleteFlag == 'delete') {
                                if (styleSheet.cssRules) {
                                    styleSheet.deleteRule(j);
                                } else {
                                    styleSheet.removeRule(j);
                                }
                                return true;
                            } else {
                                return cssRule;
                            }
                        }
                    }
                    j ++;
                } while (cssRule);
            }
        }
        return false;
    },
     
    removeRule: function (ruleName) {   
        return self.getRule(ruleName, 'delete');
    },
     
    addRule: function (ruleName) {
        if (!!document.styleSheets) {
            if (!self.getRule(ruleName)) {
                if (document.styleSheets[0].addRule) {
                    document.styleSheets[0].addRule(ruleName, null, 0);
                } else {
                    document.styleSheets[document.styleSheets.length - 1].insertRule(ruleName + ' {}', 0);
                }
            }
        }
        return self.getRule(ruleName);
    }
});