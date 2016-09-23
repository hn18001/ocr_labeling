#!/usr/bin/python
# -*- coding: utf-8 -*-
import os, sys
import time, datetime
import json
import traceback
from werkzeug.utils import secure_filename
from flask import Flask, request, redirect, render_template, jsonify, url_for, send_from_directory
from app import app

reload(sys)
sys.setdefaultencoding('utf8')

# app = Flask(__name__)
# app.config.from_object('config')

IMAGE_FOLDER = './app/static/image/'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif'])

FULL_LIST = []
UNLABELED_LIST = []
LABELED_LIST = []

@app.route('/')
@app.route('/index')
def index():
    file_dir=IMAGE_FOLDER
    print os.getcwd()
    if not os.path.exists(file_dir):
        print "Folder doesn't exist!"
    else:
        print "Folder exists!"
    filelist = os.listdir(file_dir)
    print file_dir
    for filename in filelist:
        if os.path.isfile(file_dir+filename):
            the_name, the_type = filename.split('.')
            if allowed_file(filename):
                FULL_LIST.append(the_name)
            elif the_type == 'txt':
                LABELED_LIST.append(the_name)

    UNLABELED_LIST = list(set(FULL_LIST) - set(LABELED_LIST))

    return render_template("result.html", filelist=UNLABELED_LIST)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.',1)[1] in ALLOWED_EXTENSIONS

# @app.route('/image_upload', methods=['GET','POST'])
# def image_upload():
#     ret = {}
#     ret['success'] = 0
#     ret['msg'] = ''
#     ret['link'] = ''
#     if request.method == 'POST':
#         # image_url = request.form.get('info')
#         # if image_url:
#         #     print image_url
#         file_dir=UPLOAD_FOLDER
#         if not os.path.exists(file_dir):
#             os.makedirs(file_dir)
#         f = request.files['imagedata']
#         filename = f.filename
#         if f and allowed_file(f.filename):
#             fname=secure_filename(f.filename)
#             f.save(os.path.join(file_dir,fname))
#             ret['success'] = 1
#             ret['msg'] = "Image has been uploaded."
#             ret['link'] = url_for('result',filename=fname,class_id=0)
#             print ret['link']
#     else:
#         ret['success'] = 0
#         ret['msg'] = 'Image upload failed.'
#
#     jsonstr = json.dumps(ret)
#
#     return jsonstr

@app.route('/extract_search', methods=['POST'])
def extract_search():
    ret = {}
    ret['success']=0
    ret['msg']=''
    start_time0 = datetime.datetime.now()
    try:
        infostr = request.form['info']
        print 'infostr: ',(infostr)
        info = json.loads(infostr)

        rectstr = info.get('rect')
        use_rect = False
        rect = None
        if rectstr:
            use_rect = True
            rect = json.loads(rectstr)
            print 'rect: ',rect['x1'],rect['y1'],rect['w'],rect['h']

        contstr = info.get('content')
        if contstr:
            print 'content: ', contstr

        # writing data preparation
        cont = json.loads(contstr)
        image_name = cont['filename'].split('/')[-1]
        dictstr = {'name':image_name, 'rect':rect, 'text':cont['text']}
        # put information into a txt file
        fname = IMAGE_FOLDER + cont['index'] + '.txt'
        print "save file: ", fname
        with open(fname,'w') as f:
            f.write(repr(dictstr))
        f.close()

        end_time = datetime.datetime.now()
        print 'Time cost: check input:',end_time - start_time0

        ret['success'] = 1;
        ret['msg'] = 'Done';

    except Exception as err:
        # Weblogger.error(err)
        ret['success'] = 0
        ret['msg'] = str(err)
        traceback.print_exc()

    jsonstr = json.dumps(ret)

    end_time = datetime.datetime.now()
    print 'Time cost: tot:',end_time - start_time0

    return jsonstr
