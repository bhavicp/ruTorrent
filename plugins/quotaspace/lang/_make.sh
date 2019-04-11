#!/bin/sh

for lang in es fi fr hu it lv nl pl pt sk sr tr uk zh-cn cs zh-tw da de ; do
	cp -f ./en.js $lang".js"
done

