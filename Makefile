##
# Nothing beats the original Makefile for simplicity
##

# Load few settings in variables
PROJECT_ROOT = $(shell pwd)

dev:
	git clone https://github.com/devgeniem/hkih-theme.git $(PROJECT_ROOT)/_dev/hkih || true
	git clone https://github.com/devgeniem/hkih-cpt-collection.git $(PROJECT_ROOT)/_dev/hkih-cpt-collection || true
	git clone https://github.com/devgeniem/hkih-cpt-contact.git $(PROJECT_ROOT)/_dev/hkih-cpt-contact || true
	git clone https://github.com/devgeniem/hkih-cpt-landing-page.git $(PROJECT_ROOT)/_dev/hkih-cpt-landing-page || true
	git clone https://github.com/devgeniem/hkih-cpt-release.git $(PROJECT_ROOT)/_dev/hkih-cpt-release || true
	git clone https://github.com/devgeniem/hkih-cpt-translation.git $(PROJECT_ROOT)/_dev/hkih-cpt-translation || true
	git clone https://github.com/devgeniem/hkih-linkedevents.git $(PROJECT_ROOT)/_dev/hkih-linkedevents || true
	git clone https://github.com/devgeniem/hkih-sportslocations.git $(PROJECT_ROOT)/_dev/hkih-sportslocations || true
