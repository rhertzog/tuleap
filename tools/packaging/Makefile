RPM_DIST=$(HOME)/rpmbuild/yum
PKG_NAME=tuleap

help:
	@echo "Choose one target"
	@echo "	Available targets are: `grep '^[^	]*:' Makefile | grep -v %| cut -d: -f1 | xargs echo`"

build:
	make all dist

dependanciessrc: 
	@echo "Create dependancies SRPMS"
	make -C rpm/SPECS srpms

dependancies: 
	@echo "Build dependancies RPMS"
	make -C rpm/SPECS

depdist: dependanciessrc dependancies  
	@echo "Build dependancies repository"
	make -C rpm/SPECS dist RPM_DIST=$(RPM_DIST)

%:
	@echo "Make codendi package target $@ with PKG_NAME=$(PKG_NAME)"
	make -C tools/rpm $@ PKG_NAME=$(PKG_NAME) RPM_DIST=$(RPM_DIST)

# Build SRPMS with mock
mockbuild: dependanciessrc
	@echo "Mock Build, be patient"
	make -C rpm/SRPMS
