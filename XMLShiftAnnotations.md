# Class Annotations #
|`@XmlRootElement`|Specify what the root element of the XML representation for this class will be.|
|:----------------|:------------------------------------------------------------------------------|
|`@XmlNamespace`|Specify the namespace this document will use.|


# Property Annotations #
|`@XmlIncludeWhenEmpty`| Includes the property in the output XML, even when it's unset. This will of course result in an empty element or attribute.|
|:---------------------|:---------------------------------------------------------------------------------------------------------------------------|
|`@XmlContainerElement`| Provides a "wrapping" element for the property. Use in combination with `@XmlAttribute`.|
|`@XmlElement`|This property will be represented as an XML element, with it's string representation as TextNode. The element name will be the property name.|
|`@XmlRef`|The referenced object's XML representation will be a child node in this object's XML representation.|
|`@XmlRef`|Like `@XmlRef`, but on an array of values|
|`@XmlRefLink`| The referenced object will be represented by an element containing a link attribute to this object's resource, provided a `LinkResolver` is available.|
|`@XmlRefLinkMany`|Like `@XmlRefLink`, but on an array of values|
|`@XmlAttribute`|This property's string representation will be presented as an attribute to the parent attribute. This will be the class' root node, unless `@XmlElement` is present as well.|
|`@XmlTextnode`|The value of this scalar value will be the nodeValue of the parent element|