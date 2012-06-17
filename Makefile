JS_CLOSURE = closure-js.jar
CSS_CLOSURE = closure-css.jar
JAVA = java

all: build

build: js/login.min.js js/event.min.js js/calendar.min.js
build: js/form.min.js js/member.min.js
build: css/calendar.min.css css/form.min.css

%.min.css: %.css
	$(JAVA) -jar $(CSS_CLOSURE) $^ > $@ || (rm -f $@; false)

%.min.js: %.js
	$(JAVA) -jar $(JS_CLOSURE) $^ > $@ || (rm -f $@; false)

js/form.min.js: js/jquery-ui.js js/jquery.form.js js/jquery.timepicker.js
css/form.min.css: css/jquery.timepicker.css
