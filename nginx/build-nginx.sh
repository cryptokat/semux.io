#!/bin/bash

VERSION=1.13.12
TARGET=./nginx-src
if [ ! -d "$TARGET" ]; then
        wget -O nginx-${VERSION}.tar.gz "http://nginx.org/download/nginx-${VERSION}.tar.gz"
        tar -zxvf nginx-${VERSION}.tar.gz -C ${TARGET}
fi
cd ${TARGET}

./configure --user=www-data --group=www-data --prefix=/usr/local/nginx --pid-path=/run/nginx.pid \
        --with-http_ssl_module --with-http_v2_module \
        --with-http_realip_module \
        --with-http_geoip_module \
        --with-http_gunzip_module \
        --with-http_stub_status_module \
        --with-file-aio --with-poll_module --with-threads --with-select_module --with-libatomic \
        --with-stream --with-stream_ssl_module --with-stream_realip_module --with-stream_geoip_module \
        --with-pcre --with-pcre-jit

make -j`nproc`
sudo make install
