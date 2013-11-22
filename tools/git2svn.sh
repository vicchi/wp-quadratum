#!/bin/bash

rsync --recursive --progress --exclude='.git*' --exclude='.DS_Store' --exclude='assets' . ~/Projects/svn/wp-quadratum/trunk/
rsync --recursive --progress --exclude='.git*' --exclude='.DS_Store' --exclude='upload' --exclude='*.pxm' ./assets/* ~/Projects/svn/wp-quadratum/assets/