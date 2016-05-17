INSTALL	= install
CHKCONFIG = /sbin/chkconfig
prefix	:= /usr/local

install:
	@test -d $(prefix) || mkdir -p $(prefix); \
	$(INSTALL) -d -m 755 server /usr/local/fop2; \
	$(INSTALL) --backup=numbered -m 644 server/buttons.cfg.sample /usr/local/fop2/; \
	$(INSTALL) --backup=numbered -m 644 server/autobuttons.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/extensions_override_freepbx.conf /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_en.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_es.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_de.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_it.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_zh.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_fr_FR.cfg /usr/local/fop2/; \
	$(INSTALL) -b -m 644 server/op_lang_pt_BR.cfg /usr/local/fop2/; \
	if [ -f /usr/local/fop2/fop2.cfg ]; then \
		$(INSTALL) -m 644 server/fop2.cfg /usr/local/fop2/fop2.cfg.new; \
		echo "New configuration file /usr/local/fop2/fop2.cfg.new installed. Original fop2.cfg preserved."; \
	else \
		$(INSTALL) -b -m 644 server/fop2.cfg /usr/local/fop2/; \
	fi; \
	if [ -f /usr/local/fop2/FOP2Callbacks.pm ]; then \
		$(INSTALL) --backup=numbered -m 644 server/FOP2Callbacks.pm.sample /usr/local/fop2/; \
	else \
		$(INSTALL) --backup=numbered -m 644 server/FOP2Callbacks.pm.sample /usr/local/fop2/FOP2Callbacks.pm; \
	fi; \
	$(INSTALL) -m 751 server/fop2_server /usr/local/fop2/; \
	$(INSTALL) --backup=numbered -m 751 server/*.sh /usr/local/fop2/; \
	if [ -x /var/www/html ]; then \
		$(INSTALL) -d -m 755 html /var/www/html/fop2; \
		$(INSTALL) -d -m 755 html/css /var/www/html/fop2/css; \
		$(INSTALL) -d -m 755 html/images /var/www/html/fop2/images; \
		$(INSTALL) -d -m 755 html/js /var/www/html/fop2/js; \
		$(INSTALL) -d -m 755 html/lang /var/www/html/fop2/lang; \
		$(INSTALL) -d -m 755 html/lib /var/www/html/fop2/lib; \
		mkdir -p /var/www/html/fop2/uploads; \
		chmod 777 /var/www/html/fop2/uploads; \
		if [ -f /var/www/html/fop2/config.php ]; then \
			cp /var/www/html/fop2/config.php /var/www/html/fop2/config.php.original; \
		fi; \
		cp -rp html/* /var/www/html/fop2/; \
		cp -rp html/.ht* /var/www/html/fop2/; \
		if [ -f /var/www/html/fop2/config.php.original ]; then \
			cp /var/www/html/fop2/config.php /var/www/html/fop2/config.php.new; \
			cp /var/www/html/fop2/config.php.original /var/www/html/fop2/config.php; \
			rm -f /var/www/html/fop2/config.php.original; \
			rm -f /var/www/html/fop2/config.php~; \
			echo "New configuration file /var/www/html/fop2/config.php.new installed. Original config.php preserved."; \
		fi; \
	elif [ -x /var/www ]; then \
		$(INSTALL) -d -m 755 html /var/www/fop2; \
		$(INSTALL) -d -m 755 html/css /var/www/fop2/css; \
		$(INSTALL) -d -m 755 html/images /var/www/fop2/images; \
		$(INSTALL) -d -m 755 html/js /var/www/fop2/js; \
		$(INSTALL) -d -m 755 html/lang /var/www/fop2/lang; \
		$(INSTALL) -d -m 755 html/lib /var/www/fop2/lib; \
		mkdir -p /var/www/fop2/uploads; \
		chmod 777 /var/www/fop2/uploads; \
		if [ -f /var/www/fop2/config.php ]; then \
			cp /var/www/fop2/config.php /var/www/fop2/config.php.original; \
		fi; \
		cp -rp html/* /var/www/fop2/; \
		cp -rp html/.ht* /var/www/fop2/; \
		if [ -f /var/www/fop2/config.php.original ]; then \
			cp /var/www/fop2/config.php /var/www/fop2/config.php.new; \
			cp /var/www/fop2/config.php.original /var/www/fop2/config.php; \
			rm -f /var/www/fop2/config.php.original; \
			rm -f /var/www/fop2/config.php~; \
			echo "New configuration file /var/www/fop2/config.php.new installed. Original config.php preserved."; \
		fi; \
	elif [ -x /srv/www/htdocs ]; then \
		$(INSTALL) -d -m 755 html /srv/www/htdocs/fop2; \
		$(INSTALL) -d -m 755 html/css /srv/www/htdocs/fop2/css; \
		$(INSTALL) -d -m 755 html/images /srv/www/htdocs/fop2/images; \
		$(INSTALL) -d -m 755 html/js /srv/www/htdocs/fop2/js; \
		$(INSTALL) -d -m 755 html/lang /srv/www/htdocs/fop2/lang; \
		$(INSTALL) -d -m 755 html/lib /srv/www/htdocs/fop2/lib; \
		mkdir -p /srv/www/htdocs/fop2/uploads; \
		chmod 777 /srv/www/htdocs/fop2/uploads; \
		if [ -f /srv/www/htdocs/fop2/config.php ]; then \
			cp /srv/www/htdocs/fop2/config.php /srv/www/htdocs/fop2/config.php.original; \
		fi; \
		cp -rp html/* /srv/www/htdocs/fop2/; \
		cp -rp html/.ht* /srv/www/htdocs/fop2/; \
		if [ -f /srv/www/htdocs/fop2/config.php.original ]; then \
			cp /srv/www/htdocs/fop2/config.php /srv/www/htdocs/fop2/config.php.new; \
			cp /srv/www/htdocs/fop2/config.php.original /srv/www/htdocs/fop2/config.php; \
			rm -f /srv/www/htdocs/fop2/config.php.original; \
			rm -f /srv/www/htdocs/fop2/config.php~; \
			echo "New configuration file /srv/www/htdocs/fop2/config.php.new installed. Original config.php preserved."; \
		fi; \
	else \
		echo "Could not install client. No suitable target found."; fi; \
	if [ -f /etc/debian_version ]; then \
		$(INSTALL) -m 755 init/fop2.debian /etc/init.d/fop2; \
		update-rc.d fop2 defaults; \
	elif [ -f /etc/redhat-release ]; then \
		$(INSTALL) -m 755 init/fop2.redhat /etc/rc.d/init.d/fop2; \
		${CHKCONFIG} --add fop2 ; \
	fi
	@echo "Done!"

clean:
	@rm -rf /usr/local/fop2
	@rm -rf /var/www/html/fop2
	@if [ -x /etc/rc.d/init.d/fop2 ]; then \
		rm -f /etc/rc.d/init.d/fop2; fi;
	@if [ -x /etc/init.d/fop2 ]; then \
		rm -f /etc/init.d/fop2; fi;

.PHONY: install

