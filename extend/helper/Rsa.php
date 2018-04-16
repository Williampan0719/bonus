<?php
/**
 * Author: cuidongming
 * CreateTime: 2016/5/31 13:18
 * Description:
 */
namespace extend\helper;


class Rsa
{
    private $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCpsOp7fCynf0vT1jiRFbb3rqLIicuMq4NdbisK+WMJYzA+eCMF
yBrYcmUEKh/3WTsHZrEn6zIgFFcl57y4Upcc/Y+MJJ/x2Kl/fq5FkNflVik87GOs
Xm6KtIr4zptxQGZCzHEKWDXmAzokIQUjQnSJ+Tph4/VeKFmDrV3eOZak9QIDAQAB
AoGBAJpjf4OoX8xpUilDb6X5NsY02qBQVqT+639XG8xZSFUjLKK5lUvOWzZxJh1a
4kieo8lBEo+6OpnbR8sSA69EXX0kRe8WYzX63gnMGxa2we/K3SbUkpg8C2sVgzWb
GLCzt8zIiCqFhd6mQuNA0H1lUgvOCY/9GmDtM379AY4jd98BAkEA2ea72sVGKpI/
FQ54kl+XOOeJtLxekJBB6WcJklE06kwxja9Ou+1+7miBnV5jkkJv/dy2oT5Z1lHC
O7A+v8dsQQJBAMdcSqS8FDFhsPd0rL0a5powdHieaK6ghQIV4tomsjaGvRkklB4C
XC8pHkwdFNW+VtO1+7TodYQ/dPOSRyccW7UCQB4uA/TAAADjcpNDBtYXIUXDY+JB
eMODB24BVGUMlEyjMvXRwxDqSvtQRCt8qEPYqdQ6Xp0kmqLBfipwNbCwOUECQEVi
SJRG5Rw+rNGi6M+0Ahgdzxt/vl0wfro4Fcjo+NNjV4LdqTM8jQrY27OP14WAhkWO
q7jNwsCxOSngeiURiJUCQDcFLN7TG88jKkzpt+lJJZqQ/vOPm8/xqioVQ+x7oVvz
UJfsCrexPCvOzfR9UX0cLXBHGvgWL10+hMbEhoVEI4U=
-----END RSA PRIVATE KEY-----';

    private $pub_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpsOp7fCynf0vT1jiRFbb3rqLI
icuMq4NdbisK+WMJYzA+eCMFyBrYcmUEKh/3WTsHZrEn6zIgFFcl57y4Upcc/Y+M
JJ/x2Kl/fq5FkNflVik87GOsXm6KtIr4zptxQGZCzHEKWDXmAzokIQUjQnSJ+Tph
4/VeKFmDrV3eOZak9QIDAQAB
-----END PUBLIC KEY-----';

	private $prikey = '';
	private $pubkey = '';

	public function __construct()
	{
		$this->prikey = openssl_pkey_get_private($this->private_key);
		$this->pubkey = openssl_pkey_get_public($this->pub_key);
	}

    /**
     * 私钥加密
     * @param $data
     * @return string
     */
	public function priencrypt($data)
	{
		if (openssl_private_encrypt($data, $crypted, $this->prikey)) {
			return base64_encode($crypted);
		}

		return false;
	}

    /**
     * 私钥解密
     * @param $data
     * @return string
     */
	public function pridecrypt($data)
	{
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->prikey)) {
			return $decrypted;
		}

		return false;
	}

    /**
     * 公钥加密
     * @param $data
     * @return string
     */
	public function pubencrypt($data)
	{
		if (openssl_public_encrypt($data, $encrypted, $this->pubkey)) {
			return base64_encode($encrypted);
		}

		return false;
	}

    /**
     * 公钥解密
     * @param $data
     * @return string
     */
	public function pubdecrypt($data)
	{
		if (openssl_public_decrypt(base64_decode($data), $decrypted, $this->pubkey)) {
			return $decrypted;
		}

		return false;
	}
}
