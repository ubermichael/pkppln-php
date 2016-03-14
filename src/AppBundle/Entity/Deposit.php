<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Deposit
 *
 * @ORM\Table()
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="DepositRepository")
 */
class Deposit
{
    /**
     * Database ID
     * 
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The journal that sent this deposit.
     *
     * @var Journal
     * 
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="deposits")
     * @ORM\JoinColumn(name="journal_id", referencedColumnName="id")
     */
    private $journal;
	
    /**
     * Serialized list of licensing terms as reported in the ATOM deposit.
     * 
     * @ORM\Column(type="array")
     * @var array
     */
    private $license;
    
    /**
     * Bagit doesn't understand compressed files that don't have a file
     * extension. So set the file type, and build file names from that.
     *
     * @var string
     * @ORM\Column(type="string", nullable=false);
     */
    private $fileType;
    
    /**
     * Deposit UUID, as generated by the PLN plugin.
     *
     * @var string
     * 
     * @Assert\Uuid
     * @ORM\Column(type="string", length=36, nullable=false)
     */
    private $depositUuid;

    /**
     * When the deposit was received.
     *
     * @var string
     * 
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $received;

    /**
     * The deposit action (add, edit)
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $action;
            
    /**
     * The issue volume number
     *
     * @var int
     * 
     * @ORM\Column(type="integer", nullable=false)
     */
    private $volume;
    
    /**
     * The issue number for the deposit.
     *
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $issue;

    /**
     * Publication date of the deposit content.
     *
     * @var string
     * @ORM\Column(type="date")
     */
    private $pubDate;
    
    /**
     * The checksum type for the deposit (SHA1, MD5)
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $checksumType;

    /**
     * The checksum value, in hex.
     *
     * @var string
     * @Assert\Regex("/^[0-9a-f]+$/");
     * @ORM\Column(type="string")
     */
    private $checksumValue;
    
    /**
     * The source URL for the deposit. This may be a very large string.
     *
     * @var string
     * 
     * @Assert\Url
     * @ORM\Column(type="string", length=2048)
     */
    private $url;

    /**
     * Size of the deposit, in bytes.
     *
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $size;
    
    /**
     * Current processing state
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    private $state;
    
    /**
     *
     * @var array
     * @ORM\Column(type="array", nullable=false)
     */
    private $errorLog;

    /**
     * Stae of the deposit in LOCKSSOMatic or the PLN.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $plnState;

    /**
     * Size of the processed package file, ready for deposit to LOCKSS.
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packageSize;

    /**
     * Path to the processed package file.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packagePath;

    /**
     * Processed package checksum type.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumType;

    /**
     * Checksum for the processed package file.
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumValue;

    /**
     * Date the deposit was sent to LOCKSSOmatic or the PLN.
     *
     * @var date
     * @ORM\Column(type="date", nullable=true)
     */
    private $depositDate;
    
    /**
     * URL for the deposit receipt.
     *
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", length=2048)
     */
    private $depositReceipt;

    /**
     * Processing log for this deposit.
     * 
     * @var string
     * @ORM\Column(type="text")
     */
    private $processingLog;

    /**
     * Construct an empty deposit.
     */
    public function __construct() {
		$this->license = array();
		$this->received = new DateTime();
		$this->processingLog = '';
		$this->state = "depositedByJournal";
        $this->errorLog = array();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set deposit_uuid
     *
     * @param string $depositUuid
     * @return Deposit
     */
    public function setDepositUuid($depositUuid)
    {
        $this->depositUuid = strtoupper($depositUuid);

        return $this;
    }

    /**
     * Get deposit_uuid
     *
     * @return string 
     */
    public function getDepositUuid()
    {
        return $this->depositUuid;
    }

    /**
     * Set received
     *
     * @param DateTime $received
     * @return Deposit
     */
    public function setReceived($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * Get received
     *
     * @return DateTime
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Deposit
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     * @return Deposit
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set issue
     *
     * @param integer $issue
     * @return Deposit
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get issue
     *
     * @return integer 
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Set pubDate
     *
     * @param DateTime $pubDate
     * @return Deposit
     */
    public function setPubDate(DateTime $pubDate)
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    /**
     * Get pubDate
     *
     * @return DateTime
     */
    public function getPubDate()
    {
        return $this->pubDate;
    }

    /**
     * Set checksumType
     *
     * @param string $checksumType
     * @return Deposit
     */
    public function setChecksumType($checksumType)
    {
        $this->checksumType = $checksumType;

        return $this;
    }

    /**
     * Get checksumType
     *
     * @return string 
     */
    public function getChecksumType()
    {
        return $this->checksumType;
    }

    /**
     * Set checksumValue
     *
     * @param string $checksumValue
     * @return Deposit
     */
    public function setChecksumValue($checksumValue)
    {
        $this->checksumValue = strtoupper($checksumValue);

        return $this;
    }

    /**
     * Get checksumValue
     *
     * @return string 
     */
    public function getChecksumValue()
    {
        return $this->checksumValue;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Deposit
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Deposit
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Deposit
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set plnState
     *
     * @param string $plnState
     * @return Deposit
     */
    public function setPlnState($plnState)
    {
        $this->plnState = $plnState;

        return $this;
    }

    /**
     * Get plnState
     *
     * @return string 
     */
    public function getPlnState()
    {
        return $this->plnState;
    }
    
    public function setComment($comment) {
        $this->comment = $comment;
        
        return $this;
    }
    
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set depositDate
     *
     * @param DateTime $depositDate
     * @return Deposit
     */
    public function setDepositDate(DateTime $depositDate)
    {
        $this->depositDate = $depositDate;

        return $this;
    }

    /**
     * Get depositDate
     *
     * @return DateTime
     */
    public function getDepositDate()
    {
        return $this->depositDate;
    }

    /**
     * Set depositReceipt
     *
     * @param string $depositReceipt
     * @return Deposit
     */
    public function setDepositReceipt($depositReceipt)
    {
        $this->depositReceipt = $depositReceipt;

        return $this;
    }

    /**
     * Get depositReceipt
     *
     * @return string 
     */
    public function getDepositReceipt()
    {
        return $this->depositReceipt;
    }

    /**
     * Set journal
     *
     * @param Journal $journal
     * @return Deposit
     */
    public function setJournal(Journal $journal = null)
    {
        $this->journal = $journal;

        return $this;
    }

    /**
     * Get journal
     *
     * @return Journal
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestamp() {
        $this->received = new DateTime();
    }
    
    /**
     * return a string representation fo the deposit, which is the deposit's 
     * UUID.
     * 
     * @return type
     */
    public function __toString() {
        return $this->depositUuid;
    }

    /**
     * Set file_type
     *
     * @param string $fileType
     * @return Deposit
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get file_type
     *
     * @return string 
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Get the file name of the packaged up bag, based on its file type.
     *
     * @return string
     */
    public function getFileName() {
        $extension = '';
        switch($this->getFileType()) {
            case 'application/zip':
                $extension = '.zip';
                break;
            case 'application/x-gzip':
                $extension = '.tgz';
                break;
        }
        return $this->getDepositUuid() . $extension;
    }

    /**
     * Get the processing history for the deposit.
     *
     * @return string
     */
    public function getProcessingLog() {
        return $this->processingLog;
    }

    /**
     * Append to the processing history.
     *
     * @param string $content
     */
    public function addToProcessingLog($content) {
        $date = date(DateTime::ATOM);
        $this->processingLog .= "{$date}\n{$content}\n\n";
    }

    /**
     * Set packageSize
     *
     * @param integer $packageSize
     * @return Deposit
     */
    public function setPackageSize($packageSize)
    {
        $this->packageSize = $packageSize;

        return $this;
    }

    /**
     * Get packageSize
     *
     * @return integer 
     */
    public function getPackageSize()
    {
        return $this->packageSize;
    }

    /**
     * Set packagePath
     *
     * @param string $packagePath
     * @return Deposit
     */
    public function setPackagePath($packagePath)
    {
        $this->packagePath = $packagePath;

        return $this;
    }

    /**
     * Get packagePath
     *
     * @return string 
     */
    public function getPackagePath()
    {
        return $this->packagePath;
    }

    /**
     * Set packageChecksumType
     *
     * @param string $packageChecksumType
     * @return Deposit
     */
    public function setPackageChecksumType($packageChecksumType)
    {
        $this->packageChecksumType = $packageChecksumType;

        return $this;
    }

    /**
     * Get packageChecksumType
     *
     * @return string 
     */
    public function getPackageChecksumType()
    {
        return $this->packageChecksumType;
    }

    /**
     * Set packageChecksumValue
     *
     * @param string $packageChecksumValue
     * @return Deposit
     */
    public function setPackageChecksumValue($packageChecksumValue)
    {
        $this->packageChecksumValue = $packageChecksumValue;

        return $this;
    }

    /**
     * Get packageChecksumValue
     *
     * @return string 
     */
    public function getPackageChecksumValue()
    {
        return $this->packageChecksumValue;
    }

    /**
     * Set license
     *
     * @param array $license
     * @return Deposit
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }
	
	public function addLicense($key, $value) {
		$this->license[$key] = $value;
	}

    /**
     * Get license
     *
     * @return array 
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set processingLog. 
     * 
     * @todo should this be removed? Doctrine can set the processing log without
     * it, and there aren't really any use cases for setting the deposit log
     * otherwise.
     *
     * @param string $processingLog
     * @return Deposit
     */
    public function setProcessingLog($processingLog)
    {
        $this->processingLog = $processingLog;

        return $this;
    }

    /**
     * Set errorLog
     *
     * @param array $errorLog
     * @return Deposit
     */
    public function setErrorLog($errorLog)
    {
        $this->errorLog = $errorLog;

        return $this;
    }
    
    public function addErrorLog($error) {
        $this->errorLog[] = $error;
        return $this;
    }

    /**
     * Get errorLog
     *
     * @return array 
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }
}
