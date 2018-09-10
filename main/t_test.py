#import system library
import os, sys
import requests, urllib.request
import pymysql
from bs4 import BeautifulSoup
import json

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import function as fnc
from datetime import datetime


sql = cMysql.cMysql(con.DB_INFO)
sql.db_conn()

p_cd = 'SPR20180417000038714'

param = []
qry = """
	SELECT * FROM TB_PRODUCT WHERE PRODUCT_CD = %s
"""
param.append(p_cd)
result = sql.exec('data', qry, param)
cnt = result['cnt']
ds = result['data']
brand_cd = ds['BRAND_CD']


# input url
# print('크롤링할 웹페이지의 URL을 입력하세요 : ')
url = "http://www.amorepacificmall.com/plist/11064/all/all/all/detail.do?i_sProductcd="+ p_cd

# HTTP GET Request
print(str(datetime.now()))

req = requests.get(url)
html = req.text

# make soup
soup = BeautifulSoup(html, 'html.parser')

# set variables
dic_main = {}


# 이미지 내려받기
list_main_img = soup.find("ul", {"class" : "dR_img"}).find_all('li')

arr_main_img = []
for ds_img in list_main_img:
	isVod = fnc.IS_ELEMENT(ds_img.find('div', {'class' : 'video_inner'}))
	
	if isVod == False :
		img_url = ds_img.find('img').get('src')		# 이미지 url추출

		cnt = img_url.rfind("/") + 1		# 파일명 시작위치
		fullfilename = img_url[cnt:]		# 전체파일명
		filename = fullfilename.split(".")[0]		# 파일명
		ext = fullfilename.split(".")[1]			# 확장자

		#cust_filename = filename +"_"+ str(_NOW_DT.microsecond) +"."+ ext
		cust_filename = brand_cd +"/"+ fullfilename
		urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

		arr_main_img.append(cust_filename)
	#end if
#end for


# 상품명
obj_title = soup.find("div", {"class" : "nameR_pd"})
product_nm2 = obj_title.text.strip()

# 가격
#obj_price = soup.find("div", {"class" : "nameR_pay"}).find("dl", {"class" : "base_pay"})
#is_sale = str(obj_price.find("dd", {"class" : "delT"}))
#
#if is_sale == "None" :
#	price = obj_price.find("dd", {"class" : "baseT"}).find("b").text.strip()
#else :
#	price = obj_price.find("dd", {"class" : "delT"}).find("del").text.strip()
#
#price = price[0 : len(price) - 1].replace(",", "")

# 옵션정보
arr_option = []
is_option = fnc.IS_ELEMENT(soup.find("div", {"id" : "listOptScroll1"}))

if is_option == True :
	list_option = soup.find("div", {"id" : "listOptScroll1"}).find_all("dl", {"class" : "op_sell"})

	for obj_option in list_option :
		dic_option = {}
		optname = obj_option.find("dt").text.strip()
		optname = optname.replace("(일시품절)", "").strip()
		is_sale = fnc.IS_ELEMENT(obj_option.find("dd").find("del"))

		if is_sale == True :
			optprice = obj_option.find("dd").find("del").text.strip()
		else :
			optprice = obj_option.find("span", {"class" : "ff_rbt"}).text.strip()

		optprice = optprice.replace("원", "").replace(",", "")
		dic_option["title"] = optname
		dic_option["price"] = optprice

		arr_option.append(dic_option)
		#print(optname +" : "+ optprice)
else :
	dic_option = {}

	opt = soup.select('.optionBoxList.1st')[0]
	optname = opt.select('.op_name')[0].text.strip()
	optprice = opt.select('.op_pay')[0].text.strip()

	optname = optname.replace("[일시품절]", "").strip()
	optprice = optprice.replace("원", "").replace(",", "")

	dic_option["title"] = optname
	dic_option["price"] = optprice
	arr_option.append(dic_option)
#end if


# 상품상세이미지
#ds_img = soup.find("", {"id" : "reN_infoP"}).find("img")
#
#img_url = ds_img.get("src")
#
#cnt = img_url.rfind("/") + 1		# 파일명 시작위치
#fullfilename = img_url[cnt:]		# 전체파일명
#filename = fullfilename.split(".")[0]		# 파일명
#ext = fullfilename.split(".")[1]			# 확장자
#
##cust_filename = filename +"_"+ str(_NOW_DT.microsecond) +"."+ ext
#cust_filename = brand_cd +"/"+ fullfilename
#urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드
#
#arr_desc_img = cust_filename

list_desc_img = soup.find("", {"id" : "reN_infoP"}).find_all('img')

arr_desc_img = []
for ds_img in list_desc_img :
	img_url = ds_img.get("src")

	cnt = img_url.rfind("/") + 1		# 파일명 시작위치
	fullfilename = img_url[cnt:]		# 전체파일명
	filename = fullfilename.split(".")[0]		# 파일명
	ext = fullfilename.split(".")[1]			# 확장자

	#cust_filename = filename +"_"+ str(_NOW_DT.microsecond) +"."+ ext
	cust_filename = brand_cd +"/"+ fullfilename
	urllib.request.urlretrieve(img_url, con._UPLOAD_DIR +"/"+ cust_filename)		# 다운로드

	arr_desc_img.append(cust_filename)

# make main data
dic_main["main_img"] = arr_main_img
dic_main["product_nm2"] = product_nm2
dic_main["option"] = arr_option
dic_main["desc_img"] = arr_desc_img

json_main = json.dumps(dic_main)

#os.system("clear")
print(json_main)

print(str(datetime.now()))