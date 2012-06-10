JS_CLOSURE = closure-js.jar
CSS_CLOSURE = closure-css.jar
JAVA = java

all: build

build: css/calendar.min.css js/login.min.js js/jquery.form.min.js js/event.min.js js/calendar.min.js

%.min.css: %.css
	$(JAVA) -jar $(CSS_CLOSURE) $< > $@ || (rm -f $@; false)

%.min.js: %.js
	$(JAVA) -jar $(JS_CLOSURE) $< > $@ || (rm -f $@; false)
