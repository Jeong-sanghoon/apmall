import sys
import requests
import pymysql
from bs4 import BeautifulSoup
from datetime import datetime

sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import function as fnc
from _lib import cMysql

now_dt = datetime.now()

# input url
# print('크롤링할 웹페이지의 URL을 입력하세요 : ')
url = "http://www.amorepacificmall.com/plist/11197/CTG002/CTG025/CTG029/detail.do?i_sProductcd=P00001901"

# HTTP GET Request
req = requests.get(url)
html = req.text

dic_main = {}

# make soup
soup = BeautifulSoup(html, 'html.parser')

# find tag
list_main_img = soup.find("ul", {"class" : "dR_img"}).find_all('li')

for ds_img in list_main_img:
	img_url = ds_img.find('img').get('src')

ds_title = soup.find("div", {"class" : "nameR_pd"})
title = ds_title.text.strip()

dic_main['img'] = img_url
dic_main['title'] = title

print(dic_main['img'])
print(dic_main['title'])


# db connect
#conn = pymysql.connect(host="39.116.31.100", user="ns9", passwd="ns9!@34", db="NS9", charset="utf8")
#cur = conn.cursor(pymysql.cursors.DictCursor)
#
#qry = """INSERT INTO ttt (dt, img, title) VALUES (%s, %s, %s)"""
#
#cur.execute(qry, (str(now_dt), str(dic_main['img']), str(dic_main['title'])))
#conn.commit()

cMysql = cMysql.cMysql(con.DB_INFO)
cMysql.db_conn()
#cMysql.tran()

qry = """INSERT INTO ttt (dt, img, title) VALUES (%s, %s, %s)"""
param = [str(now_dt), str(dic_main['img']), str(dic_main['title'])]

cMysql.exec(qry, param)
#cMysql.commit()

cMysql.close()