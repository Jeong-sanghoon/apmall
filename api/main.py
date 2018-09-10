#import system library
import sys
import requests
import urllib.request			#이미지 다운로드
import json
import time
from datetime import datetime
from bs4 import BeautifulSoup	#크롤링
from flask import Flask, request
from flask_restful import Resource, Api

# import user library
sys.path.insert(0, '/home/crawler')
from _lib import config as con
from _lib import cMysql
from _lib import cLogger
from _lib import function as fnc

# import def
import defData


# set logger
cLog = cLogger.cLogger("api/main")
logger = cLog.set_logger()

app = Flask(__name__)


@app.route("/api/main/", methods=['GET', 'POST'])
def make_product_data() :
	#print("상품URL을 입력하세요 : ")
	#url = "http://www.amorepacificmall.com/plist/11197/CTG002/CTG025/CTG029/detail.do?i_sProductcd=P000019012354"
	#url = "http://www.amorepacificmall.com/plist/11064/CTG001/CTG023/all/detail.do?i_sProductcd=SPR20150423000008426"
	url = request.args.get('url')
	
	logger.debug('url == '+ url)
	try :
		objBs = defData.getHtmlObj(url)					# html object 가져오기
		p_cd = defData.getProductCode(objBs)			# product code 가져오기
		dsPrd = defData.getProductInfo(p_cd)			# 상품정보 가져오기

		if fnc.IS_BOOL(dsPrd) == True :
			product_seq = dsPrd['PRODUCT_SEQ']
		else :
			product_nm = objBs.find('form', {'id':'frm'}).find('input', {'id':'i_sProductnm'}).get('value')			# 상품명 가져오기
			product_nm2 = objBs.find("div", {"class" : "nameR_pd"}).text.strip()									# 상세상품명 가져오기
			product_desc = objBs.find("p", {"class" : "nicknameArea"}).text.strip()									# 상품설명 가져오기
			brand_cd = objBs.find("div", {"class" : "nameR_brand"}).find('a').get('id')								# 브랜드코드 가져오기
			brand_nm = objBs.find("div", {"class" : "nameR_brand"}).find('a').text.strip()							# 브랜드명 가져오기
			
			price = defData.getPrice(objBs)									# 상품가격 가져오기
			dicCate = defData.getCategory(objBs)							# 카테고리 가져오기
			arrMainImg = defData.getMainImg(objBs, brand_cd)				# 상품메인이미지 가져오기
			arrOption = defData.getOption(objBs, product_nm, price)			# 옵션정보 가져오기
			arrDetailImg = defData.getDetailImg(objBs, brand_cd)			# 상품설명이미지 가져오기

			dic_info = {}
			dic_info['product_cd'] = p_cd
			dic_info['product_nm'] = product_nm
			dic_info['product_nm2'] = product_nm2
			dic_info['product_desc'] = product_desc
			dic_info['brand_cd'] = brand_cd
			dic_info['brand_nm'] = brand_nm
			dic_info['price'] = price
			dic_info['category'] = dicCate
			dic_info['main_img'] = arrMainImg
			dic_info['option'] = arrOption
			dic_info['desc_img'] = arrDetailImg

			product_seq = defData.setMainInfoInsert(dic_info)

			dsPrd = defData.getProductInfo2(product_seq)
		#end if

		# 옵션리스트, 이미지리스트 불러오기
		rsOpt = defData.getOptionList(product_seq)
		rsImg = defData.getImageList(product_seq)

		rsMainImg = []
		rsDetImg = []
		for ds in rsImg :
			if ds['IMG_TP'] == 'M' :
				rsMainImg.append(ds['FILE_NM'])
			elif ds['IMG_TP'] == 'D' :
				rsDetImg.append(ds['FILE_NM'])
			#end if
		#end for

		rtnData = dsPrd
		rtnData['option'] = rsOpt
		rtnData['main_image'] = rsMainImg
		rtnData['detail_image'] = rsDetImg
		jsonData = json.dumps(rtnData, default=fnc.JSON_DEFAULT)
		
		logger.debug("================ product info ================")
		logger.debug("return data : "+ str(jsonData))
		logger.debug("================ product info ================")

		return jsonData

	except Exception as err :
		logger.error("find error : "+ str(err))
	#end try
#end def


if __name__ == '__main__':
	app.run(host="0.0.0.0", port="5000", debug=True)